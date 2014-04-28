<?php

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class AppRjKernel extends AppKernel
{
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
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Fp\BadaBoomBundle\FpBadaBoomBundle($this->exceptionCatcher, $this->chainNodeManager),
            new Rj\EmailBundle\RjEmailBundle(),
            new Payum\Bundle\PayumBundle\PayumBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new APY\JsFormValidationBundle\APYJsFormValidationBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new JMS\JobQueueBundle\JMSJobQueueBundle(),

            new CreditJeeves\CoreBundle\CreditJeevesCoreBundle(),
            new CreditJeeves\CoreBundle\CoreBundle(),
            new CreditJeeves\DataBundle\DataBundle(),
            new CreditJeeves\ComponentBundle\ComponentBundle(),
            new CreditJeeves\SimulationBundle\SimulationBundle(),
            new CreditJeeves\PublicBundle\PublicBundle(),
            new CreditJeeves\ExperianBundle\ExperianBundle(),
            new CreditJeeves\UserBundle\UserBundle(),
            new CreditJeeves\CheckoutBundle\CheckoutBundle(),
            new CreditJeeves\AdminBundle\AdminBundle(),
            new CreditJeeves\ApplicantBundle\ApplicantBundle(),

            // Must be last in the list
            new RentJeeves\AdminBundle\RjAdminBundle(),
            new RentJeeves\DataBundle\RjDataBundle(),
            new RentJeeves\TenantBundle\TenantBundle(),
            new RentJeeves\LandlordBundle\LandlordBundle(),
            new RentJeeves\PublicBundle\RjPublicBundle(),
            new RentJeeves\ComponentBundle\RjComponentBundle(),
            new RentJeeves\CoreBundle\RjCoreBundle(),
            new RentJeeves\CheckoutBundle\RjCheckoutBundle(),
            new RentJeeves\ExperianBundle\RjExperianBundle(),
            new CreditJeeves\ApiBundle\ApiBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test', 'migration'))) {
            $bundles[] = new ENC\Bundle\BackupRestoreBundle\BackupRestoreBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Khepin\YamlFixturesBundle\KhepinYamlFixturesBundle();
            $bundles[] = new CreditJeeves\DevBundle\CjDevBundle();
        }
        if (in_array($this->getEnvironment(), array('test', 'migration'))) {
            $bundles[] = new Behat\MinkBundle\MinkBundle();
            $bundles[] = new CreditJeeves\TestBundle\TestBundle(); // Must be one of last included bundle
            $bundles[] = new RentJeeves\TestBundle\TestBundle(); // Must be last included bundle
        }
    
        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/rj/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        return $this->rootDir.'/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'rj';
    }
}
