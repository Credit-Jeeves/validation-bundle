<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use CreditJeeves\ExperianBundle\NetConnect\PreciseIDQuestions;
use JMS\Serializer\SerializationContext;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use RentJeeves\CheckoutBundle\Services\UserDetailsTypeProcessor;
use RentJeeves\CoreBundle\Controller\TenantController as Controller;
use RentJeeves\DataBundle\Entity\Tenant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SummaryController extends Controller
{
    /**
     * @Route("/summary", name="tenant_summary")
     * @Template()
     */
    public function indexAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();
        if (UserIsVerified::PASSED != $user->getIsVerified()) {
            return new RedirectResponse(
                $this->generateUrl('personal_info_fill_pidkiq')
            );
        }

        $sEmail = $user->getEmail();
        $report  = $this->getReport();

        if (!$report) {
            return $this->forward(
                'TenantBundle:Report:get',
                [
                    'redirect' => 'tenant_summary',
                ]
            );
        } elseif (!$report->getRawData()) {
            return $this->forward(
                'TenantBundle:Report:get',
                [
                    'redirect' => 'tenant_summary',
                    'shouldUpdateReport' => true
                ]
            );
        }

        $score = $this->getScore();

        return [
            'sEmail' => $sEmail,
            'Report' => $report,
            'Score' => $score,
            'User' => $user,
            'creditTrackEnabled' => $user->getSettings()->isCreditTrack(),
        ];
    }

    /**
     * @Route("/pidkiq/personal/info", name="personal_info_fill_pidkiq")
     * @Template()
     */
    public function personalInfoFillPidkiqAction(Request $request)
    {
        $personalInfoForm = $this->createForm(new UserDetailsType($this->getUser()), $this->getUser());

        $personalInfoForm->handleRequest($request);

        if ($personalInfoForm->isValid()) {
            /** @var $formProcessor UserDetailsTypeProcessor */
            $formProcessor = $this->get('user.details.type.processor');
            $formProcessor->save($personalInfoForm, $this->getUser());

            return $this->redirect($this->generateUrl('pidkiq_questions'));
        }
        $addressChoice = $personalInfoForm->get('address_choice')->getData();
        if ($personalInfoForm->isSubmitted()) {
            $defaultAddressId = (!empty($addressChoice)) ? $addressChoice->getId() : null;
        } else {
            $defaultAddressId = ($address = $this->getUser()->getDefaultAddress()) ? $address->getId() : null;
        }

        $addressesJson = $this->get('jms_serializer')->serialize(
            $this->getUser()->getAddresses(),
            'json',
            SerializationContext::create()->setGroups(array('paymentAccounts'))
        );

        return array(
            'form'             => $personalInfoForm->createView(),
            'addressesJson'        => $addressesJson,
            'defaultAddressId' => $defaultAddressId,
        );
    }

    /**
     * @Route("/pidkiq/questions", name="pidkiq_questions")
     * @Template()
     */
    public function questionsAction(Request $request)
    {
        /**
         * @var $user Tenant
         */
        $user = $this->getUser();
        $address = $user->getDefaultAddress();
        $ssn = $user->getSsn();
        $dateOfBirth = $user->getDateOfBirth();

        if (empty($ssn) || empty($address) || empty($dateOfBirth)) {
            return $this->redirect($this->generateUrl('personal_info_fill_pidkiq'));
        }

        if (UserIsVerified::PASSED == $this->getUser()->getIsVerified()) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }

        /**
         * @var $pidkiqQuestions PreciseIDQuestions
         */
        $pidkiqQuestions = $this->get('experian.net_connect.precise_id.questions');

        if ($pidkiqQuestions->processQuestions()) {
            $form = $this->createForm(new QuestionsType($pidkiqQuestions->getQuestionsData()));
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($pidkiqQuestions->processForm($form)) {
                    return $this->redirect($this->generateUrl('tenant_summary'));
                }
                //Setup not valid answer
                $pidkiqQuestions->setError(
                    $this->get('translator')->trans(
                        'pidkiq.error.incorrect.answer2',
                        array(
                            '%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'),
                            '%MAIN_LINK%'     => $this->container->getParameter('external_urls')['user_voice'],
                        )
                    )
                );

            }

            return array(
                'form'  => $form->createView(),
                'error' => $pidkiqQuestions->getError(),
            );
        }

        return array(
            'form'  => null,
            'error' => $pidkiqQuestions->getError(),
        );

    }
}
