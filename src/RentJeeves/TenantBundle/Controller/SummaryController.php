<?php

namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\ExperianBundle\Form\Type\QuestionsType;
use CreditJeeves\ExperianBundle\Services\PidkiqQuestions;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
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
        $user = $this->getUser();
        if (UserIsVerified::PASSED != $user->getIsVerified()) {
            return new RedirectResponse(
                $this->get('router')->generate('personal_info_fill_pidkiq')
            );
        }

        $sEmail = $user->getEmail();
        $Report  = $this->getReport();

        if (!$Report) {
            return $this->forward('ExperianBundle:Report:get');
        }

        $Score = $this->getScore();
        return array(
            'sEmail' => $sEmail,
            'Report' => $Report,
            'Score' => $Score,
            'User' => $user,
        );
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
            list($isNewAddress, $address) = $this->get('process.user.details.type')->process(
                $personalInfoForm,
                $this->getUser()
            );
            return $this->redirect($this->generateUrl('pidkiq_questions'));
        }

        return array(
            'form'      => $personalInfoForm->createView(),
            'addresses' => $this->getUser()->getAddresses(),
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
        $dateOfBirthday = $user->getDateOfBirth();

        if (empty($ssn) || empty($address) || empty($dateOfBirthday)) {
            return $this->redirect($this->generateUrl('personal_info_fill_pidkiq'));
        }

        if (UserIsVerified::PASSED == $this->getUser()->getIsVerified()) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }

        /**
         * @var $pidkiqQuestions PidkiqQuestions
         */
        $pidkiqQuestions = $this->get('pidkiq.questions');

        if ($questions = $pidkiqQuestions->retrieveQuestions()) {
            $form = $this->createForm(new QuestionsType($questions));
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
        }

        return array(
            'form'  => $form->createView(),
            'error' => $pidkiqQuestions->getError(),
        );
    }
}
