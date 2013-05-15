<?php

use BadaBoom\Adapter\Logger\NativeLoggerAdapter;
use BadaBoom\Adapter\Mailer\NativeMailerAdapter;
use BadaBoom\ChainNode\Provider\EnvironmentProvider;
use BadaBoom\ChainNode\Provider\ExceptionStackTraceProvider;
use BadaBoom\ChainNode\Provider\ExceptionSubjectProvider;
use BadaBoom\ChainNode\Provider\ExceptionSummaryProvider;
use BadaBoom\ChainNode\Provider\ServerProvider;
use BadaBoom\ChainNode\Sender\LogSender;
use BadaBoom\ChainNode\Sender\MailSender;
use BadaBoom\Serializer\Encoder\TextEncoder;
use BadaBoom\DataHolder\DataHolder;
use Fp\BadaBoomBundle\ChainNode\Provider\SessionProvider;
use Fp\BadaBoomBundle\ChainNode\SafeChainNodeManager;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ChainNode\SymfonyExceptionHandlerChainNode;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Serializer\Serializer;

class AppKernel extends Kernel
{
    /**
     * @var \Fp\BadaBoomBundle\ExceptionCatcher\ExceptionCatcherInterface
     */
    protected $exceptionCatcher;

    /**
     * @var \Fp\BadaBoomBundle\ChainNode\ChainNodeManagerInterface
     */
    protected $chainNodeManager;

    /**
     * @var SymfonyExceptionHandlerChainNode
     */
    protected $symfonyExceptionHandlerChainNode;

    public function registerBundles()
    {
        $bundles = array(
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new CreditJeeves\UserBundle\UserBundle(),
            new CreditJeeves\CoreBundle\CoreBundle(),
            new CreditJeeves\ApplicantBundle\ApplicantBundle(),
            new CreditJeeves\DealerBundle\DealerBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new CreditJeeves\DataBundle\DataBundle(),
            new CreditJeeves\ComponentBundle\ComponentBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new CreditJeeves\PublicBundle\PublicBundle(),
            new Fp\BadaBoomBundle\FpBadaBoomBundle($this->exceptionCatcher, $this->chainNodeManager),
            new CreditJeeves\ExperianBundle\ExperianBundle(),
            new Rj\EmailBundle\RjEmailBundle(),
            new CreditJeeves\AdminBundle\AdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }
        if (in_array($this->getEnvironment(), array('test'))) {
            $bundles[] = new Behat\MinkBundle\MinkBundle();
            $bundles[] = new CreditJeeves\TestBundle\TestBundle(); // Must be last included bundle
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();
        $parameters['project.root'] = dirname($this->getRootDir());
        $parameters['web.dir'] = $parameters['project.root'] . '/web';
        $parameters['data.dir'] = $parameters['project.root'] . '/data';
        $parameters['web.upload.dir'] = $parameters['web.dir'] . '/uploads';
        return $parameters;
    }

    public function init()
    {
        $this->exceptionCatcher = new ExceptionCatcher;
        $this->chainNodeManager = new SafeChainNodeManager;

        $this->exceptionCatcher->start($this->isDebug());

        $this->initializeChainNodeManager();

        foreach ($this->chainNodeManager->all() as $chainNode) {
            $this->exceptionCatcher->registerChainNode($chainNode);
        }
    }

    public function initializeChainNodeManager()
    {
        $this->symfonyExceptionHandlerChainNode = new SymfonyExceptionHandlerChainNode($this->isDebug());
        $this->chainNodeManager->addSender('default', $this->symfonyExceptionHandlerChainNode);

        // prod env
        if ('dev' != $this->getEnvironment()) {
            $recipients = array('forma@66ton99.org.ua, systems@creditjeeves.com, alex.emelyanov.ua@gmail.com');

            $this->chainNodeManager->addProvider('default', new ExceptionSubjectProvider());
            $this->chainNodeManager->addProvider('default', new ExceptionSummaryProvider());
            $this->chainNodeManager->addProvider('default', new ExceptionStackTraceProvider());
            $this->chainNodeManager->addProvider('default', new ServerProvider());
            $this->chainNodeManager->addProvider('default', new SessionProvider());
            $this->chainNodeManager->addProvider('default', new EnvironmentProvider());

            $serializer = new Serializer(
                array(/*new DataHolderNormalizer()*/),
                array(new TextEncoder())
            );

            touch($logFile = $this->getRootDir().'/logs/'.$this->getEnvironment().'-exceptions.log');
            $this->chainNodeManager->addSender(
                'default',
                new LogSender(
                    new NativeLoggerAdapter($logFile),
                    $serializer,
                    new DataHolder(
                        array(
                            'format' => 'text'
                        )
                    )
                )
            );

            $domain = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'my.creditjeeves.com';
            $this->chainNodeManager->addSender(
                'default',
                new MailSender(
                    new NativeMailerAdapter,
                    $serializer,
                    new DataHolder(array(
                        'sender' => 'noreply@'.$domain,
                        'recipients' => $recipients,
                        'subject' => 'Whoops, looks like something went wrong.',
                        'format' => 'text',
                        'headers' => array()
                    ))
                )
            );
        }
    }
}
