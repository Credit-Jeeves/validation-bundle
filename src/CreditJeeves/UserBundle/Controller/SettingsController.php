<?php
namespace CreditJeeves\UserBundle\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\PasswordType;
use CreditJeeves\ApplicantBundle\Form\Type\ContactType;
use CreditJeeves\ApplicantBundle\Form\Type\NotificationType;
use CreditJeeves\ApplicantBundle\Form\Type\RemoveType;
use CreditJeeves\CoreBundle\Controller\ApplicantController;
use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\AddressRepository;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\UserBundle\Form\Type\UserAddressType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SettingsController extends Controller
{
    /**
     * @Route("/password", name="user_password")
     * @Route("/landlord/password", name="landlord_password")
     * @Route("/profile/")
     * @Template()
     */
    public function passwordAction()
    {
        $request = $this->get('request');
        /** @var \CreditJeeves\DataBundle\Entity\User $user */
        $user = $this->getUser();
        $oldPassword = $user->getPassword();
        $email = $user->getEmail();
        $form = $this->createForm(new PasswordType(), $user);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $user = $form->getData();
                $reEnteredPassword = $this->container->get('user.security.encoder.digest')
                    ->encodePassword($user->getPassword(), $user->getSalt());
                if ($oldPassword == $reEnteredPassword) {
                    $formName = $request->request->get($form->getName());
                    $sNewPassword = $this->container->get('user.security.encoder.digest')
                        ->encodePassword($formName['password_new']['Password'], $user->getSalt());
                    $user->setPassword($sNewPassword);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('notice', 'Information has been updated');
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'password.error.old_not_match'
                    );
                }
            }
        }

        return array(
            'sEmail' => $email,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/contact", name="user_contact")
     * @Template()
     */
    public function contactAction()
    {
        $request = $this->get('request');
        /**
         * @var $user User
         */
        $user = $this->getUser();
        $form = $this->createForm(new ContactType(), $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'contact.information.update');
        }
        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/email", name="user_email")
     * @Template()
     */
    public function emailAction()
    {
        $request = $this->get('request');
        $cjUser = $this->getUser();
        $sEmail = $cjUser->getEmail();
        $form = $this->createForm(new NotificationType(), $cjUser);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($cjUser);
                $em->flush();
                $this->get('session')->getFlashBag()->add('notice', 'Information has been updated');
            }
        }

        return array(
            'sEmail' => $sEmail,
            'form' => $form->createView()
        );

    }

    /**
     * @Route("/remove", name="user_remove")
     * @Template()
     */
    public function removeAction()
    {
        $request = $this->get('request');
        /** @var User $User */
        $User = $this->getUser();
        $sEmail = $User->getEmail();
        $sPassword = $User->getPassword();
        $form = $this->createForm(new RemoveType(), $User);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $reEnteredPassword = $this->container->get('user.security.encoder.digest')
                ->encodePassword($User->getPassword(), $User->getSalt());
            if ($sPassword == $reEnteredPassword) {
                $this->get('remove.user')->remove($User);
                // Commented for develop
                return $this->redirect($this->generateUrl('public_message_flash'));
            } else {
                $this->get('session')->getFlashBag()->add('notice', 'Incorrect Password');
            }
        }

        return array(
            'sEmail' => $sEmail,
            'form'   => $form->createView()
        );
    }

    /**
     * @Route("/addresses", name="user_addresses")
     * @Template()
     */
    public function addressesAction()
    {
        /** @var User $User */
        $User = $this->getUser();
        /** @var AddressRepository $repository */
        $repository = $this->getDoctrine()->getRepository('DataBundle:Address');
        $addresses = $repository->createQueryBuilder('addr')
            ->where('addr.user = :user')
            ->setParameter('user', $User->getId())
            ->orderBy('addr.isDefault', 'DESC')
            ->addOrderBy('addr.id')
            ->getQuery()
            ->getResult();

        return compact('addresses');
    }

    /**
     * @Route("/address/{id}", name="user_address_add_edit", requirements={"id" = "\d+|new"})
     * @Template()
     */
    public function addressAddEditAction($id)
    {
        /** @var Request $request */
        $request = $this->get('request');
        /** @var User $user */
        $user = $this->getUser();

        if ('new' != $id) {
            $address = $this->getDoctrine()->getRepository('DataBundle:Address')->findOneBy(
                [
                    'id' => $id,
                    'user' => $user->getId()
                ]
            );

            if (empty($address)) {
                throw $this->createNotFoundException('This item does not exist.');
            }

            $headTitle = 'settings.address.head.edit';

        } else {
            $address = new Address();
            $address->setUser($user);

            $headTitle = 'settings.address.head.add';
        }

        $form = $this->createForm(new UserAddressType($user->getType()), $address);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->isValid()) {
                if ($address->getIsDefault()) {
                    $this->getDoctrine()
                        ->getRepository('DataBundle:Address')
                        ->resetDefaults($user->getId());
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($address);
                $em->flush();

                return $this->redirect($this->generateUrl('user_addresses'));
            }
        }

        return [
            'headTitle' => $headTitle,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route(
     *     "/address-delete/{id}",
     *     name="user_address_delete",
     *     requirements={"id" = "\d+"},
     *     options={"expose"=true}
     * )
     */
    public function addressDeleteAction($id)
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var Address $address */
        $address = $this->getDoctrine()->getRepository('DataBundle:Address')->findOneBy(
            [
                'id' => $id,
                'user' => $user->getId()
            ]
        );

        if (empty($address)) {
            throw $this->createNotFoundException('This item does not exist.');
        }

        if ($address->getIsDefault()) {
            $this->get('session')->getFlashBag()->add(
                'notice',
                'You can not delete the default address. Set up another address as default.'
            );

            return $this->redirect($this->generateUrl('user_addresses'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($address);
        $em->flush();

        $this->get('session')->getFlashBag()->add('notice', 'The address was deleted successfully.');

        return $this->redirect($this->generateUrl('user_addresses'));
    }
}
