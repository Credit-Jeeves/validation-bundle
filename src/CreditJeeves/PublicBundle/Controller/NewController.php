<?php

namespace CreditJeeves\PublicBundle\Controller;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Enum\GroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\LeadNewType;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\DataBundle\Entity\Group;
use Symfony\Component\HttpFoundation\Request;

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
        $vehicles = array();
        $makes = array();
        $prepare = $this->get('data.utility.vehicle')->getVehicles();
        foreach ($prepare as $make => $model) {
            $makes[] = $make;
            $vehicles[] = $model;
        }
        if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('applicant_homepage'));
        }
        /** @var Request $request */
        $request = $this->get('request');
        $data = $request->request->all();
        $index = 0;
        if (isset($data['creditjeeves_applicantbundle_leadnewtype']['target_name']['make'])) {
            $index = $data['creditjeeves_applicantbundle_leadnewtype']['target_name']['make'];
        }
        $query = $request->query;
        $Lead = new Lead();
        $User = new Applicant();
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
                'em'                => $this->getDoctrine()->getManager(),
                'vehicles'          => $this->get('data.utility.vehicle')->getVehicles(),
            )
        );
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $Lead = $form->getData();
                if ($this->validateLead($Lead)) {
                    // prepare target name and target url
                    if (GroupType::VEHICLE == $Group->getType()) {
                        $target = $Lead->getTargetName();
                        $make = $makes[$target['make']];
                        $models = $vehicles[$target['make']];
                        $result = array();
                        foreach ($models as $name => $url) {
                            $result[] = array($name, $url);
                        }
                        $model = $result[$target['model']];
                        $Lead->setTargetName($make.' '.$model[0]);
                        $Lead->setTargetUrl($model[1]);
                    } else {
                        $Lead->setTargetName('');
                        $Lead->setTargetUrl('');
                    }
                    $Lead->setTargetScore($Lead->getGroup()->getTargetScore());
                    $Lead->setDealer($Lead->getGroup()->getMainDealer());
                    $User = $Lead->getUser();
                    // We need to check if such user already exists in the system
                    $email = $User->getEmail();
                    $check = $this->
                        getDoctrine()->
                        getRepository('DataBundle:User')->
                            findOneBy(
                                array(
                                    'email' => $email,
                                )
                            );
                    if ($check) {
                        // @todo move to login page and create page to add lead
                        $password = $User->getPassword();
                        $isPasswordValid = $this->container->
                            get('user.security.encoder.digest')->
                            isPasswordValid(
                                $check->getPassword(),
                                $password,
                                $check->getSalt()
                            );
                        if ($isPasswordValid) {
                            $User = $check;
                            $Lead->setUser($User);
                            $em->persist($User);
                            $em->persist($Lead);
                            $em->flush();
                        } else {
                            $i18n = $this->get('translator');
                            $this->get('session')->
                                getFlashBag()->
                                add(
                                    'message_title',
                                    $i18n->trans(
                                        'error.user.absent.title'
                                    )
                                );
                            $this->get('session')->
                                getFlashBag()->
                                add(
                                    'message_body',
                                    $i18n->trans(
                                        'error.user.absent.text'
                                    )
                                );

                            return $this->redirect($this->generateUrl('public_message_flash'));
                        }
                    } else {
                        $User->setCulture($request->getLocale());
                        $User->setType(UserType::APPLICANT);
                        $User->getDefaultAddress()->setUser($User); // TODO it can be done more clear
                        $User->setPassword(
                            $this->container->get('user.security.encoder.digest')
                                ->encodePassword($User->getPassword(), $User->getSalt())
                        );
                        $em->persist($User);
                        $em->persist($Lead);
                        $em->flush();
                    }
                    $this->get('core.session.applicant')->setLeadId($Lead->getId());
                    $this->get('core.session.applicant')->setUser($User);
                    $this->get('project.mailer')->sendCheckEmail($User);

                    return $this->redirect($this->generateUrl('applicant_new_send'));

                } else {
                    $translator = $this->get('translator');
                    $this->get('session')->getFlashBag()->add(
                        'notice',
                        $translator->trans(
                            'leads.user.exists',
                            array('%GROUP_NAME%'=> $Lead->getGroup()->getName())
                        )
                    );
                }
            }
        }

        return array(
            'form' => $form->createView(),
            'type' => $Group->getType(),
            'vehicles' => json_encode($vehicles),
        );
    }

    private function validateLead($Lead)
    {
        $Group = $Lead->getGroup();
        if (empty($Group)) {
            return false;
        }
        $email = $Lead->getUser()->getEmail();
        $user = $this->
            getDoctrine()->
            getRepository('DataBundle:User')->
            findOneBy(
                array(
                    'email' => $email,
                )
            );
        if (!$user) {
            return true; //This is new applicant
        }
        $nGroupId = $Group->getId();
        $nLeads = $this->
            getDoctrine()->
            getRepository('DataBundle:Lead')->
            findBy(
                array(
                    'cj_applicant_id' => $user->getId(),
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
