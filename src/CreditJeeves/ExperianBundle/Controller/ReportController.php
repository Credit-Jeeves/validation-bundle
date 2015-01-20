<?php
namespace CreditJeeves\ExperianBundle\Controller;

use CreditJeeves\CoreBundle\Enum\ScoreModelType;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\ReportType;
use CreditJeeves\DataBundle\Entity\ReportD2c;
use CreditJeeves\ExperianBundle\NetConnect\Exception;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Guzzle\Http\Exception\CurlException;
use RentJeeves\DataBundle\Enum\CreditSummaryVendor;
use RentJeeves\ExternalApiBundle\Services\Transunion\TransUnionUserCreatorTrait;
use RentTrack\TransUnionBundle\CCS\Model\TransUnionUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ExperianBundle\NetConnect\CreditProfile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 * @Route("/report")
 *
 * @method \CreditJeeves\DataBundle\Entity\User getUser()
 */
class ReportController extends Controller
{
    use TransUnionUserCreatorTrait;

    protected $reportType = ReportType::PREQUAL;

    protected $redirect = null;

    /**
     * @var \CreditJeeves\ExperianBundle\NetConnect
     */
    protected $creditProfile;

    /**
     * @TODO incapsulate this logic to a corresponding report type object
     * @TODO doesn't work when $isD2c = true and vendor = Transunion
     *
     * @return bool
     */
    protected function isReportLoadAllowed($isD2c = false)
    {
        $vendor = $this->container->getParameter('credit_services')['credit_summary_vendor'];
        switch ($vendor) {
            case CreditSummaryVendor::EXPERIAN:
                if ($isD2c) {
                    return $this->getUser()->getLastCompleteReportOperation();
                }
                return !$this->getUser()->getReportsPrequal()->last();
            case CreditSummaryVendor::TRANSUNION:
                return !$this->getUser()->getReportsTUSnapshot()->last();
            default:
                throw new Exception(sprintf('Unknown credit summary vendor \'%s\'', $vendor));
        }
    }

    /**
     * @Route("/get/d2c", name="core_report_get_d2c")
     * @Template("ExperianBundle:Report:get.html.twig")
     *
     * @return array
     */
    public function getD2cAction()
    {
        $this->get('session')->getFlashBag()->set('isD2cReport', true);
//        $this->redirect = ;
        return $this->getAction(null, true);
    }

    /**
     * @Route("/get", name="core_report_get")
     * @Route("/get/{redirect}", name="core_report_get")
     * @Template()
     *
     *
     *
     * @return array
     */
    public function getAction($redirect = null, $isD2c = false)
    {
        if (!$this->isReportLoadAllowed($isD2c)) {
            throw $this->createNotFoundException('Report does not allowed');
        }
        return array(
            'url' => $this->generateUrl('core_report_get_ajax'),
            'redirect' => $redirect?$this->generateUrl($redirect):null
            //$this->getRequest()->headers->get('referer'), //FIXME redirect does not preserve referer
        );
    }

    protected function getArf($isD2c = false)
    {
        $this->creditProfile = $this->get('experian.net_connect.credit_profile');
        if ($isD2c) {
            $this->creditProfile->initD2c();
        }
        return $this->creditProfile->getResponseOnUserData($this->get('core.session.applicant')->getUser());
    }

    protected function createScore($scoreValue, EntityManager $em)
    {
        if ($scoreValue > 1000) {
            return false;
        }

        $score = new Score();
        $score->setUser($this->getUser());
        $score->setScore($scoreValue);
        $em->persist($score);
    }

    protected function saveCreditSummary($isD2c = false)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$this->isReportLoadAllowed($isD2c)) {
            return false;
        }
        $user = $this->getUser();
        $vendor = $this->container->getParameter('credit_services')['credit_summary_vendor'];
        switch ($vendor) {
            case CreditSummaryVendor::EXPERIAN:
                if ($isD2c) {
                    $report = $this->getUser()->getLastCompleteReportOperation()->getReportD2c();
                } else {
                    $report = new ReportPrequal();
                    $report->setUser($user);
                }
                $report->setRawData($this->getArf($isD2c));
                $em->persist($report);

                // RT uses only VANTAGE3
                $newScore = $report->getArfReport()->getScore(ScoreModelType::VANTAGE3);
                $this->createScore($newScore, $em);

                $em->flush();
                return true;
            case CreditSummaryVendor::TRANSUNION:
                $transUnionUser = $this->getTransUnionUser($user);

                $snapshot = $this
                    ->get('transunion.ccs.credit_snapshot')
                    ->getSnapshot(
                        $transUnionUser,
                        $this->container->getParameter('transunion.renttrack_snapshot_bundle')
                    );
                $report = new ReportTransunionSnapshot();
                $report->setRawData($snapshot);
                $report->setUser($user);
                $em->persist($report);

                $newScore = $this
                    ->get('transunion.ccs.vantage_score_3')
                    ->getScore(
                        $transUnionUser,
                        $this->container->getParameter('transunion.renttrack_vantage_score_3_bundle')
                    );

                $this->createScore($newScore, $em);

                $em->flush();
                return true;
            default:
                throw new Exception(sprintf('Unknown credit summary vendor \'%s\'', $vendor));
        }
    }

    /**
     * @Route(
     *  "/get_ajax",
     *  name="core_report_get_ajax",
     *  defaults={"_format"="json"},
     *  requirements={"_format"="html|json"}
     * )
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function getAjaxAction()
    {
        if ($this->getRequest()->isMethod('POST')) {
            $session = $this->getRequest()->getSession();
            ignore_user_abort();
            set_time_limit(90);
            if (false == $session->get('cjIsReportProcessing', false)) {
                $session->set('cjIsReportProcessing', true);
                $isD2cReport = $this->get('session')->getFlashBag()->get('isD2cReport');
                try {
                    $this->saveCreditSummary($isD2cReport);
                } catch (DBALException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    $this->get('session')->getFlashBag()->set(
                        'message_title',
                        $this->get('translator.default')->trans('error.fatal.title')
                    );
                    $this->get('session')->getFlashBag()->set(
                        'message_body',
                        $this->get('translator.default')->trans(
                            'error.fatal.message-%SUPPORT_EMAIL%',
                            array('%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'))
                        )
                    );
                    return new JsonResponse(array('url' => $this->generateUrl('public_message_flash')));
                } catch (CurlException $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    $this->get('session')->getFlashBag()->set('isD2cReport', $isD2cReport);
                    $session->set('cjIsReportProcessing', false);
                    return new JsonResponse('warning');
                } catch (Exception $e) {
                    $this->get('fp_badaboom.exception_catcher')->handleException($e);
                    if (4000 == $e->getCode()) {
                        $this->get('session')->getFlashBag()->set('isD2cReport', $isD2cReport);
                        $session->set('cjIsReportProcessing', false);
                        return new JsonResponse('warning');
                    } else {
                        throw $e;
                    }
                }
                $session->set('cjIsReportProcessing', false);
                return new JsonResponse('finished');
            }
            return new JsonResponse('processing');
        }


        return new JsonResponse('processing');
    }
}
