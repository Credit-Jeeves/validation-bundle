<?php
namespace CreditJeeves\CoreBundle\Mailer;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\User;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use \Exception;
use \RuntimeException;

/**
 * @DI\Service("project.mailer")
 */
class Mailer extends BaseMailer implements MailerInterface
{
    public function sendInviteToApplicant($user, $sTemplate = 'invite')
    {
        return $this->sendEmail($user, $sTemplate);
    }

    public function sendWelcomeEmailToApplicant($user, $sTemplate = 'welcome')
    {
        return $this->sendEmail($user, $sTemplate);
    }

    public function sendCheckEmail($user, $sTemplate = 'check')
    {
        $url = $this->container->get('router')->generate(
            'applicant_new_check',
            array('code' => $user->getInviteCode()),
            true
        );
        
        return $this->sendEmail(
            $user,
            $sTemplate,
            array(
               'checkUrl' => $url
            )
        );
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $url = $this->container->get('router')->generate(
            'fos_user_registration_confirm',
            array('token' => $user->getConfirmationToken()),
            true
        );

        return $this->sendEmail(
            $user,
            'confirmation',
            array(
                'confirmationUrl' => $url
            )
        );
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        # \Symfony\Component\DependencyInjection\ContainerInterface::get
        $url = $this->container->get('router')->generate(
            'fos_user_resetting_reset',
            array('token' => $user->getConfirmationToken()),
            true
        );

        return $this->sendEmail(
            $user,
            'resetting',
            array(
                'confirmationUrl' => $url
            )
        );
    }

    public function sendReceipt(Order $order)
    {
        $dateShortFormat = $this->container->getParameter('date_short');
        return $this->sendEmail(
            $order->getUser(),
            'receipt',
            array(
                'date' => $order->getCreatedAt()->format($dateShortFormat),
                'amout' => '$9.00', // TODO move to config file and add correct currency formatting
                'number' => $order->getAuthorizes()->last()->getTransactionId(),
            )
        );
    }

//     public function sendRjCheckEmail($user, $sTemplate = 'rjCheck')
//     {
//         $url = $this->container->get('router')->generate(
//             'tenant_new_check',
//             array('code' => $user->getInviteCode()),
//             true
//         );
        
//         return $this->sendEmail(
//             $user,
//             $sTemplate,
//             array(
//                'checkUrl' => $url
//             )
//         );
//     }

//     public function sendRjLandLordInvite($landlord, $tenant, $contract, $sTemplate = 'rjLandLordInvite')
//     {
//         $vars = array(
//             'nameLandlord'          => $landlord->getFirstName(),
//             'fullNameTenant'        => $tenant->getFullName(),
//             'nameTenant'            => $tenant->getFirstName(),
//             'address'               => $contract->getProperty()->getAddress(),
//             'unitName'              => ($contract->getUnit())? $contract->getUnit()->getName() : null,
//             'inviteCode'            => $landlord->getInviteCode(),
//         );

//         return $this->sendBaseLetter($sTemplate, $vars, $landlord->getEmail(), $landlord->getCulture());
//     }


//     public function sendRjTenantInvite($tenant, $landlord, $contract, $sTemplate = 'rjTenantInvite')
//     {
//         $unit = $contract->getUnit();
//         $vars = array(
//             'fullNameLandlord'      => $landlord->getFullName(),
//             'nameTenant'            => $tenant->getFirstName(),
//             'address'               => $contract->getProperty()->getAddress(),
//             'unitName'              => $unit ? $unit->getName() : '',
//             'inviteCode'            => $tenant->getInviteCode(),
//         );

//         return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
//     }

//     public function sendRjTenantLatePayment($tenant, $landlord, $contract, $sTemplate = 'rjTenantLatePayment')
//     {
//         $vars = array(
//                 'fullNameLandlord'      => $landlord->getFullName(),
//                 'nameTenant'            => $tenant->getFirstName(),
//                 'address'               => $contract->getProperty()->getAddress(),
//                 'unitName'              => $contract->getUnit()->getName(),
//                 'inviteCode'            => $tenant->getInviteCode(),
//         );

//         return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
//     }

//      public function sendRjLandlordComeFromInvite(
//          $tenant,
//          $landlord,
//          $contract,
//          $sTemplate = 'rjLandlordComeFromInvite'
//      )
//     {
//         $vars = array(
//                 'nameTenant'            => $tenant->getFirstName(),
//                 'fullNameLandlord'      => $landlord->getFullName(),
//                 'address'               => $contract->getProperty()->getAddress(),
//                 'unitName'              => $contract->getUnit()->getName(),
//                 'rentAmount'            => $contract->getRent(),
//                 'dueDate'               => $contract->getDueDay(),
//         );

//         return $this->sendBaseLetter($sTemplate, $vars, $tenant->getEmail(), $tenant->getCulture());
//     }
}
