<?php

use BadaBoom\Adapter\Logger\NativeLoggerAdapter;
use BadaBoom\Adapter\Mailer\NativeMailerAdapter;
use BadaBoom\ChainNode\Filter\ExceptionClassFilter;
use BadaBoom\ChainNode\Provider\EnvironmentProvider;
use BadaBoom\ChainNode\Provider\ExceptionStackTraceProvider;
use BadaBoom\ChainNode\Provider\ExceptionSubjectProvider;
use BadaBoom\ChainNode\Provider\ExceptionSummaryProvider;
use BadaBoom\ChainNode\Provider\ServerProvider;
use BadaBoom\ChainNode\Sender\LogSender;
use BadaBoom\ChainNode\Sender\MailSender;
use BadaBoom\DataHolder\DataHolder;
use Fp\BadaBoomBundle\ChainNode\Provider\SessionProvider;
use Fp\BadaBoomBundle\ChainNode\SafeChainNodeManager;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
//use Fp\BadaBoomBundle\ChainNode\SymfonyExceptionHandlerChainNode;
use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ChainNode\SymfonyExceptionHandlerChainNode;
use CreditJeeves\CoreBundle\Serializer\Encoder\HtmlEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AppKernel extends Kernel
{
    const IS_TEST = false;
    const BACKUP_FILE_NAME = 'backup.sql';
    const BACKUP_DIR_NAME = 'data/files/sql';

    /**
     * @var bool
     */
    private $catch = true;

    /**
     * @var \Fp\BadaBoomBundle\ExceptionCatcher\ExceptionCatcherInterface
     */
    protected $exceptionCatcher;

    /**
     * @var \Fp\BadaBoomBundle\ChainNode\ChainNodeManagerInterface
     */
    protected $chainNodeManager;

    public function __construct($environment, $debug, $catch = true)
    {
        parent::__construct($environment, $debug);
        $this->catch = $catch;
    }

    public function boot()
    {
        parent::boot();
        $this->container->get('translator')->setOption(
            'cache_dir',
            $this->container->get('translator')->getOption('cache_dir') . '/' . $this->getName()
        );
    }

    /**
     * @param bool $boolean
     */
    public function setCatchException($boolean)
    {
        $this->catch = (bool)$boolean;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (static::IS_TEST) {
            return parent::handle($request, $type, $this->catch);
        }

        return parent::handle($request, $type, $catch);
    }

    /**
     * This is test speed suggested by Kris Wallsmith ih his blog
     *
     * @link http://kriswallsmith.net/post/27979797907/get-fast-an-easy-symfony2-phpunit-optimization
     */
    protected function initializeContainer()
    {
        if (!static::IS_TEST) {
            return parent::initializeContainer();
        }

        static $first = true;

        $debug = $this->debug;

        if (false == $first) {
            // disable debug mode on all but the first initialization
            $this->debug = false;
        }

        // will not work with --process-isolation
        $first = false;

        try {
            parent::initializeContainer();
        } catch (\Exception $e) {
            $this->debug = $debug;
            throw $e;
        }

        $this->debug = $debug;
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
        $this->chainNodeManager->addProvider('default', new ExceptionSubjectProvider());
        $this->chainNodeManager->addProvider('default', new ExceptionSummaryProvider());
        $this->chainNodeManager->addProvider('default', new ExceptionStackTraceProvider());
        $this->chainNodeManager->addProvider('default', new ServerProvider());
        $this->chainNodeManager->addProvider('default', new SessionProvider());
        $this->chainNodeManager->addProvider('default', new EnvironmentProvider());

        // prod env
//        if ('dev' != $this->getEnvironment()) {
            $recipients = array(
                'systems@creditjeeves.com' .
                ', cary@renttrack.com'
            );

            $filter = new ExceptionClassFilter();
            $filter->allow('Exception');
            $filter->deny('SuppressedErrorException');
            $filter->deny('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
            $filter->deny('Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException');


            $this->chainNodeManager->addFilter('default', $filter);

            $serializer = new Serializer(
                array(/*new DataHolderNormalizer()*/),
                array(new HtmlEncoder())
            );

//            touch($logFile = $this->getRootDir().'/logs/'.$this->getEnvironment().'-exceptions.log');
//            $this->chainNodeManager->addSender(
//                'default',
//                new LogSender(
//                    new NativeLoggerAdapter($logFile),
//                    $serializer,
//                    new DataHolder(
//                        array(
//                            'format' => 'text'
//                        )
//                    )
//                )
//            );

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
                        'format' => 'html',
                        'headers' => array(
                            'Content-type' => 'text/html; charset=utf-8',
                        )
                    ))
                )
            );
/*        } else {
            $this->chainNodeManager->addSender(
                'default',
                new SymfonyExceptionHandlerChainNode(true)
            );
        }
 */   }
}
