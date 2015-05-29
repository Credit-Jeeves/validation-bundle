<?php

namespace RentJeeves\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use \Exception;

/**
 * Class ExceptionController
 * @package RentJeeves\AdminBundle\Controller
 *
 * This controller is here as an easy way to simulate errors in production
 * without actually causing a real problem.
 *
 * This allows us to test our alerting and monitoring systems as needed.
 */
class ExceptionController extends Controller
{
    /**
     * @Route("test/emergency", name="admin_emergency_test")
     */
    public function sendTestEmergency()
    {
        $this->get("logger")->emergency("TEST EMERGENCY: This is only a test. Go back to sleep.");

        return new Response(
            '<html><body>SUCCESS. Wrote emergency record to log</body></html>'
        );
    }

    /**
     * @Route("test/alert", name="admin_alert_test")
     */
    public function sendTestAlert()
    {
        $this->get("logger")->alert("TEST ALERT: This is only a test. Ignore me!!");

        return new Response(
            '<html><body>SUCCESS. Wrote alert record to log</body></html>'
        );
    }

    /**
     * @Route("test/exception", name="admin_exception_test")
     */
    public function sendTestException()
    {
        throw new Exception("TEST EXCEPTION: This is only a test. Ignore me!!");
    }
}
