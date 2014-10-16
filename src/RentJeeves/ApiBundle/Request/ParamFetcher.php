<?php

namespace RentJeeves\ApiBundle\Request;

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Request\ParamFetcher as Base;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Util\ViolationFormatterInterface as ViolationFormatter;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Services\Encoders\AttributeEncoderInterface;
use RentJeeves\ApiBundle\Services\Encoders\EncoderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ValidatorInterface as Validator;
use Doctrine\Common\Util\ClassUtils;
use JMS\DiExtraBundle\Annotation as DI;
use InvalidArgumentException,
    ReflectionClass;

/**
 * @DI\Service("fos_rest.request.param_fetcher")
 */
class ParamFetcher extends Base
{
    protected $paramReader;

    protected $encoderFactory;

    protected $request;

    protected $params;

    protected $controller;

    protected $paramsValue = [];

    protected $processedAttributes = [];

    /**
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

        if (isset($this->paramsValue[$name])) {
            return $this->paramsValue[$name];
        }

        if ($this->params[$name] instanceof AttributeParam) {
            return $this->processAttributeParam($name);
        }

        $value = parent::get($name, $strict);
        if ($encoder = $this->getEncoder($name)) {
            $value = $encoder->decode($value);
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

        $params = array();
        foreach ($this->params as $name => $config) {
            if ($config instanceof AttributeParam) {
                $this->processAttributeParam($name);
            } else {
                $params[$name] = $this->get($name, $strict);
            }
        }

        return $params;
    }

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
