<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Form\BaseOrderReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/reports")
 */
class ReportsController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     name="landlord_reports"
     * )
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (!$user->haveAccessToReports()) {
            throw new Exception("Don't have access");
        }

        $group = $this->get('core.session.landlord')->getGroup();
        $formBaseOrder = $this->createForm(new BaseOrderReportType($user, $group));

        $formBaseOrder->handleRequest($this->get('request'));
        if ($formBaseOrder->isValid()) {

            $data = $formBaseOrder->getData();
            $baseReport = $this->get('report.order');
            $report = $baseReport->getReport($data);

            $response = new Response();
            $response->setContent($report);
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-Type', $baseReport->getContentType());
            $response->headers->set('Content-Disposition', 'attachment; filename='.$baseReport->getFileName());

            return $response;
        }

        return array(
            'settings'           => $user->getSettings(),
            'formBaseOrder'      => $formBaseOrder->createView()
        );
    }
}
