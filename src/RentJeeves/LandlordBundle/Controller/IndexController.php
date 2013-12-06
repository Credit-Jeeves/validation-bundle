<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\LandlordBundle\Registration\MerchantAccountModel;
use RentJeeves\LandlordBundle\Registration\SAMLEnvelope;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Generator\UrlGenerator;

class IndexController extends Controller
{
    /**
     * @Route("/", name="landlord_homepage")
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getGroups();

        return array(
            'nGroups' => $groups->count(),
            'Group' => $this->getCurrentGroup(),
        );
    }

    /**
     * @Route("/complete/", name="landlord_complete_account")
     */
    public function completeAccountAction()
    {
       $landlord =  $this->get('core.session.landlord')->getUser();
       $group =  $this->get('core.session.landlord')->getGroup();

        $merchantAccount = new MerchantAccountModel('', '', '');
        $saml = new SAMLEnvelope(
            $landlord,
            $merchantAccount,
            $this->generateUrl('landlord_hps_success', array(), UrlGenerator::ABSOLUTE_URL),
            $this->generateUrl('landlord_hps_error', array(), UrlGenerator::ABSOLUTE_URL)
        );
        $signedSaml = $this->get('signature.manager')->sign($saml->getAssertionResponse());

        $depositAccount = $group->getDepositAccount();
        if (empty($depositAccount)) {
            $depositAccount = new DepositAccount($group);
            $em = $this->getDoctrine()->getManager();
            $em->persist($depositAccount);
            $em->flush();
        }

        if ($depositAccount->getStatus() == DepositAccountStatus::DA_INIT) {
            return $this->render(
                'RjPublicBundle:Landlord:redirectHeartland.html.twig',
                array(
                    'saml' => $saml->encodeAssertionResponse($signedSaml),
                    'url' => $saml::ONLINE_BOARDING
                )
            );
        }

        return $this->redirect($this->generateUrl('landlord_homepage'));
    }
}
