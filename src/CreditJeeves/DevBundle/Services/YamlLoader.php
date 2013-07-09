<?php

namespace CreditJeeves\DevBundle\Services;

use Khepin\YamlFixturesBundle\Fixture\YamlAclFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Khepin\YamlFixturesBundle\Loader\YamlLoader as KhepinYamlLoader;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 * @Service("khepin.yaml_loader")
 */
class YamlLoader extends KhepinYamlLoader 
{
    
    const LOCALE = 'locale';

    /**
     * @InjectParams({
     *    "em"                = @DI\Inject("kernel"),
     *    "bundles"           = @DI\Inject("%khepin_yaml_fixtures.resources%"),
     *    "directory"         = @DI\Inject("%khepin_yaml_fixtures.directory%")
     * })
     */
    public function __construct(\AppKernel $kernel, $bundles, $directory)
    {
        parent::__construct($kernel, $bundles, $directory);
    }

	/**
     * Loads the fixtures file by file and saves them to the database
     */
    public function loadFixtures()
    {
        $this->loadFixtureFiles();

        foreach ($this->fixture_files as $file) {
            $fixture_data = Yaml::parse($file);
            $this->placeholder($fixture_data);
            // if nothing is specified, we use doctrine orm for persistence
            $persistence = isset($fixture_data['persistence']) ? $fixture_data['persistence'] : 'orm';
            $fixture = $this->getFixtureClass($persistence);
            $fixture = new $fixture($fixture_data, $this, $file);
            $fixture->load($this->getManager($persistence), func_get_args());
        }

        if (!is_null($this->acl_manager)) {
            foreach ($this->fixture_files as $file) {
                $fixture = new YamlAclFixture($file, $this);
                $fixture->load($this->acl_manager, func_get_args());
            }
        }
    }

    protected function placeholder(&$fixture_data)
    {
    	foreach ($fixture_data['fixtures'] as $modelName => $data) {
    		foreach ($data as $modelKey => $modelValue) {
    			
    			if(is_array($modelValue)) {
    				continue;
    			}

    			$match = '';
    		 	preg_match( '/\%(.*?)\%/', $modelValue, $match);	
    		 	if(empty($match)) {
    		 		continue;
    		 	}	

    		 	$parameterName = (isset($match[1]))? $match[1] : false;
    		 	if(!$parameterName) {
    		 		continue;
    		 	}

                if(preg_match('/file:/', $parameterName)) {
                    $values = explode(':', $parameterName);
                    $valueParameter = $this->getFile($values[1]);
                } else {
        		 	$valueParameter = $this->getParameter($parameterName);
                }

    		 	if(!$valueParameter) {
    		 		continue;
    		 	}

    		 	$newModelValue = str_replace("%{$parameterName}%", $valueParameter, $modelValue);
    		 	$fixture_data['fixtures'][$modelName][$modelKey]  = $newModelValue;
    		}
    	}
    }

    protected function getFile($path)
    {   
        return file_get_contents($path);
    }

    /**
     * Get All Parameters from config
     */
    protected function getParameter($paramName)
    {   
    	$container = $this->kernel->getContainer();  

        //@TODO: Hard code, need fix it in future
        if($paramName === self::LOCALE) {
            return $container->parameters['kernel.default_locale'];
        }

        if($container->hasParameter($paramName)) {
    	   return $container->getParameter($paramName);
        }

        return false;
    }
}