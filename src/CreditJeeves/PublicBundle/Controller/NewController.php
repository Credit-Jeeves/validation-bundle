<?php

namespace CreditJeeves\PublicBundle\Controller;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Enum\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\LeadNewType;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NewController extends Controller
{
    /**
     * @Route("/new/dealer/{code}", name="applicant_new")
     * @Route("/new",              name="applicant_new_code", defaults={"code"=null})
     * @Template()
     *
     * @return array
     */
    public function indexAction($code = null)
    {
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }
        /** @var Request $request */
        $request = $this->get('request');
        $query = $request->query;
        $Lead = new Lead();

        /** @var User $User */
        $User = $this->get('core.session.applicant')->getUser();
        $Group = new Group();
        $em = $this->getDoctrine()->getManager();
        if ($code) {
            $Group = $em->getRepository('DataBundle:Group')->findOneByCode($code);
        }
        if ($request->getMethod() == 'GET') {
            // Group code
            if ($query->has('g')) {
                $Group->setCode($query->get('g'));
            }
            // User details
            $User = $this->bindUserDetails($User, $query);
            $address = new Address();
            $address->setUser($User);
            $User->addAddress($address);
            $Lead->setGroup($Group);
        }
        $Lead->setUser($User);
        $form = $this->createForm(
            new LeadNewType(),
            $Lead,
            array(
                'em' => $this->getDoctrine()->getManager()
                )
        );
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                /** @var Lead $Lead */
                $Lead = $form->getData();

                if ($this->validateLead($Lead)) {

                    $User = $Lead->getUser();
                    $User->setCulture($request->getLocale());
                    $User->setUsername($User->getEmail());
                    $User->setIsVerified(UserIsVerified::NONE);
                    $User->setType(UserType::APPLICANT);
                    $User->getDefaultAddress()->setUser($User); // TODO it can be done more clear

                    $User->setInviteCode($Lead->getGroup()->getCode());
                    $Lead->setTargetScore($Lead->getGroup()->getTargetScore());

                    //$em = $this->getDoctrine()->getManager();
                    $User->setPassword(
                        $this->container->get('user.security.encoder.digest')
                            ->encodePassword($User->getPassword(), $User->getSalt())
                    );
                    $em->persist($User);
                    $em->persist($Lead);
                    $em->flush();

                    $this->get('core.session.applicant')->setLeadId($Lead->getId());
                    $this->get('core.session.applicant')->setUser($User);
                    $this->get('project.mailer')->sendCheckEmail($User);
                    return $this->redirect($this->generateUrl('applicant_new_send'));

                } else {
                    // FIXME this text must be moved to i18n file
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        'You are already associated with this dealership. Please contact the dealership at ' .
                        $Lead->getGroup()->getName() . ' if you wish to change your salesperson.'
                    );
                }
            }
        }
        return array(
            'form' => $form->createView(),
            'type' => $Group->getType(),
        );
    }

    private function validateLead($Lead)
    {
        $Group = $Lead->getGroup();
        if (empty($Group)) {
            return false;
        }
        $nUserId = $Lead->getUser()->getId();
        $nGroupId = $Group->getId();
        $nLeads = $this->
            getDoctrine()->
            getRepository('DataBundle:Lead')->
            findBy(
                array(
                    'cj_applicant_id' => $nUserId,
                    'cj_group_id' => $nGroupId,
                )
            );
        $isExist = count($nLeads);

        return $isExist ? false : true;
    }

    /**
     * @param User $User
     * @param \Symfony\Component\HttpFoundation\ParameterBag $query
     *
     * @return User
     */
    private function bindUserDetails($User, $query)
    {
        if ($query->has('fn')) {
            $User->setFirstName($query->get('fn'));
        }
        if ($query->has('mi')) {
            $User->setMiddleInitial($query->get('mi'));
        }
        if ($query->has('ln')) {
            $User->setLastName($query->get('ln'));
        }
        if ($query->has('ea')) {
            $User->setEmail($query->get('ea'));
        }
        if ($query->has('ea')) {
            $User->setEmail($query->get('ea'));
        }
        if ($query->has('s1')) {
            $User->setStreetAddress1($query->get('s1'));
        }
        if ($query->has('s2')) {
            $User->setUnitNo($query->get('s2'));
        }
        if ($query->has('ci')) {
            $User->setCity($query->get('ci'));
        }
        if ($query->has('st')) {
            $User->setState($query->get('st'));
        }
        if ($query->has('zp')) {
            $User->setZip($query->get('zp'));
        }
        if ($query->has('ph')) {
            $User->setPhone($query->get('ph'));
        }

        return $User;
    }
}
