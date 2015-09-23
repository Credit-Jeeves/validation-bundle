<?php

namespace RentJeeves\CoreBundle\Helpers;

use Psr\Log\LoggerInterface;

/**
 * Class PeriodicExecutor
 * @package RentJeeves\CoreBundle\Helpers
 *
 * A helper class that will execute a callback method every N iterations.
 * Can be useful for periodically cleaning up after PHP in large batch routines.
 *
 */
class PeriodicExecutor
{
    /**
     * Current iteration count
     *
     * @var int
     */
    protected $counter = 1;

    /**
     * This defines the how many iterations before executing callback functions
     *
     * @var int
     */
    protected $period = 10;

    /**
     * The object that contains the $callbackMethod
     *
     * @var object
     */
    protected $callbackObject;

    /**
     * The method that will be called after $period iterations
     *
     * @var string
     */
    protected $callbackMethod;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Create a new PeriodicExecutor object that will execute the $callback function every $period iterations.
     *
     * @param $callbackObject the object that contains the $callbackMethod
     * @param $callbackMethod the method to execute
     * @param int $period the number of iterations before executing the $callbackMethod
     *
     * @throws \InvalidArgumentException if $callbackObject is not an object
     * @throws \BadMethodCallException if $callbackMethod is not a public method of $callbackObject
     */
    public function __construct($callbackObject, $callbackMethod, $period, LoggerInterface $logger)
    {
        $this->callbackObject = $callbackObject;
        $this->callbackMethod = $callbackMethod;
        $this->period = $period;
        $this->logger = $logger;
        $this->checkArguments();
        $this->logger->debug('PeriodicExecutor initialized with period of ' . $this->period);
    }

    /**
     * Increment the internal counter.  If $period iterations have passed,
     * then the $callbackMethod will be executed.
     */
    public function increment()
    {
        if ($this->counter % $this->period == 0) {
            $this->logger->debug('PeriodicExecutor executing callback method');
            call_user_func([$this->callbackObject, $this->callbackMethod]); // execute the callback!
            $this->counter = 0;
        }
        $this->counter++;
        $this->logger->debug('PeriodicExecutor incremented counter to ' . $this->counter);
    }

    /**
     * @throws \InvalidArgumentException if $callbackObject is not an object
     * @throws \BadMethodCallException if $callbackMethod is not a public method of $callbackObject
     */
    protected function checkArguments()
    {
        if (!is_object($this->callbackObject)) {
            throw new \InvalidArgumentException(
                sprintf('The callbackObject parameter must be an object')
            );
        }

        if (!is_callable([$this->callbackObject, $this->callbackMethod])) {
            throw new \BadMethodCallException(
                sprintf(
                    'The callback method "%s:%s" is not callable. Please make sure it exists and is public.',
                    get_class($this->callbackObject),
                    $this->callbackMethod
                )
            );
        }
    }
}
