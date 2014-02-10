<?php
namespace CreditJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApplicantController extends Controller
{
    /**
     * @Route("/cj/applicant/{id}/report", name="admin_applicant_report")
     */
    public function reportAction($id)
    {
        $user = $this->getDoctrine()->getRepository('DataBundle:User')->find($id);
        if (!$user) {
            throw new Exception('User not found');
        }
        $session = $this->get('session');
        try {
            $netConnect = $this->get('experian.net_connect');
            $netConnect->execute($this->container);
            $arf = $netConnect->getResponseOnUserData($user);
            if (empty($arf)) {
                throw new Exception("Empty arf string");
            }
            $report = new ReportPrequal();
            $report->setUser($user);
            $report->setRawData($arf);
            $score = $report->getReportScore();
            $em = $this->getDoctrine()->getManager();
            $em->persist($report);
            $em->flush();
            if ($score > 0) {
                $session->getFlashBag()->add('sonata_flash_success', "We get Report with Score {$score}");
            } else {
                $session->getFlashBag()->add('sonata_flash_info', "We get Report with Score {$score}");
            }
        } catch (Exception $e) {
            $this->get('fp_badaboom.exception_catcher')->handleException($e);
            $session->getFlashBag()->add('sonata_flash_error', $e->getMessage());
        }


        return new RedirectResponse(
            $this->generateUrl(
                'admin_cj_report_list',
                array(
                    'user_id' => $user->getId()
                )
            )
        );
    }
}
