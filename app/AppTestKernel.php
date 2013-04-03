<?php

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class AppTestKernel extends AppKernel
{
    private $catch;

    public function registerBundles()
    {
        $bundles = parent::registerBundles();
        return $bundles;
    }

    public function __construct($environment, $debug, $catch = false)
    {
        parent::__construct($environment, $debug);
        $this->catch = $catch;
    }

    public function setCatchException($boolean)
    {
        $this->catch = (bool)$boolean;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = false)
    {
        return parent::handle($request, $type, $this->catch);
    }

    /**
     * This is test speed suggested by Kris Wallsmith ih his blog
     *
     * @link http://kriswallsmith.net/post/27979797907/get-fast-an-easy-symfony2-phpunit-optimization
     */
    protected function initializeContainer()
    {
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
}
