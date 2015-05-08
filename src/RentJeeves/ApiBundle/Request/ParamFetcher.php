<?php

namespace RentJeeves\ApiBundle\Request;

use FOS\RestBundle\Controller\Annotations\Param;
use FOS\RestBundle\Request\ParamFetcher as Base;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Util\ViolationFormatterInterface as ViolationFormatter;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\ApiBundle\Request\Annotation\QueryParam;
use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\ApiBundle\Services\Encoders\EncoderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ValidatorInterface as Validator;
use Doctrine\Common\Util\ClassUtils;
use JMS\DiExtraBundle\Annotation as DI;
use InvalidArgumentException;
use ReflectionClass;

/**
 * @DI\Service("fos_rest.request.param_fetcher")
 */
class ParamFetcher extends Base
{
    /** @var ParamReader */
    protected $paramReader;
    /** @var EncoderFactory */
    protected $encoderFactory;
    /** @var Request */
    protected $request;
    /** @var  array */
    protected $params;
    /** @var  callable */
    protected $controller;
    /** @var array */
    protected $paramsValue = [];
    /** @var array */
    protected $processedAttributes = [];

    /**
     * @param EncoderFactory     $encoderFactory
     * @param ParamReader        $paramReader
     * @param Request            $request
     * @param ViolationFormatter $violationFormatter
     * @param Validator          $validator
     *
     * @DI\InjectParams({
     *     "encoderFactory"     = @DI\Inject("encoder_factory"),
     *     "paramReader"        = @DI\Inject("fos_rest.request.param_fetcher.reader"),
     *     "request"            = @DI\Inject("request", strict=false),
     *     "violationFormatter" = @DI\Inject("fos_rest.violation_formatter"),
     *     "validator"          = @DI\Inject("validator", strict=false)
     * })
     */
    public function __construct(
        EncoderFactory $encoderFactory,
        ParamReader $paramReader,
        Request $request,
        ViolationFormatter $violationFormatter,
        Validator $validator = null
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->paramReader = $paramReader;
        $this->request = $request;
        parent::__construct($paramReader, $request, $violationFormatter, $validator);
    }

    /**
     * {@inheritDoc}
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        parent::setController($controller);
    }

    /**
     * @param $name
     * @return null|AttributeEncoderInterface
     */
    protected function getEncoder($name)
    {
        $config = $this->params[$name];

        if (!empty($config->encoder)) {
            return $this->encoderFactory->getEncoder($config->encoder);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $strict = null)
    {
        if (null === $this->params) {
            $this->initParams();
        }

        if (array_key_exists($name, $this->paramsValue)) {
            return $this->paramsValue[$name];
        }
        /** @var Param $config */
        $config = $this->params[$name];

        if ($config instanceof AttributeParam) {
            return $this->processAttributeParam($name);
        }

        $value = parent::get($name, $strict);

        if ($encoder = $this->getEncoder($name) and ($config->strict || $value)) {
            $value = $encoder->decode($value);
        }

        if ($config->nullable && !$config->strict && $config->default === $value) {
            return $value;
        }

        if ($config instanceof RequestParam) {
            $this->request->request->set($config->getKey(), $value);
        } elseif ($config instanceof QueryParam) {
            $this->request->query->set($config->getKey(), $value);
        }

        return $this->paramsValue[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function all($strict = null)
    {
        if (null === $this->params) {
            $this->initParams();
        }

        $params = [];
        foreach ($this->params as $name => $config) {
            /** @var Param $config */
            if ($config instanceof AttributeParam) {
                $this->processAttributeParam($name);
            } elseif (!$config->strict && $config->nullable) {
                $value = $this->get($name, true);
                if ($config->default !== $value) {
                    $params[$name] = $value;
                }
            } else {
                $params[$name] = $this->get($name, $strict);
            }
        }

        return $params;
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function processAttributeParam($name)
    {
        if (isset($this->processedAttributes[$name])) {
            return true;
        }

        if ($encoder = $this->getEncoder($name) and $this->request->attributes->has($name)) {
            $value = $this->request->attributes->get($name);
            $this->request->attributes->set($name, $encoder->decode($value));
        }

        return $this->processedAttributes[$name] = true;
    }

    /**
     * Initialize the parameters
     *
     * @throws InvalidArgumentException
     */
    protected function initParams()
    {
        // controller is callable parameters and contains Controller and Action

        if (empty($this->controller)) {
            throw new InvalidArgumentException('Controller and method needs to be set via setController');
        }

        if (!is_array($this->controller) || empty($this->controller[0]) || !is_object($this->controller[0])) {
            throw new InvalidArgumentException(
                'Controller needs to be set as a class instance (closures/functions are not supported)'
            );
        }

        list($class, $method) = $this->controller;
        $reflectionClass = new ReflectionClass(ClassUtils::getClass($class));

        $this->params = $this->paramReader->read($reflectionClass, $method);
    }
}
