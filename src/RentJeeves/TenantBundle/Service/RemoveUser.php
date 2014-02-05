<?php

namespace RentJeeves\TenantBundle\Service;

use Symfony\Component\Process\Exception\LogicException;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use CreditJeeves\DataBundle\Enum\UserType;

/**
 * @Service("remove.user")
 */
class RemoveUser
{
    protected $em;

    protected $translator;

    protected $encoder;

    protected $session;

    protected $router;

    /**
     * @InjectParams({
     *     "em"             = @Inject("doctrine.orm.entity_manager"),
     *     "translator"     = @Inject("translator"),
     *     "encoder"        = @Inject("user.security.encoder.digest"),
     *     "session"        = @Inject("session"),
     *     "router"         = @Inject("router")
     * })
     */
    public function __construct($em, $translator, $encoder, $session, $router)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->router = $router;
        $this->encoder = $encoder;
        $this->session = $session;
    }

    public function remove(UserInterface $user)
    {
        if ($user->getType() !== UserType::TENANT) {
            throw new LogicException("We can remove user with type tenant, but this user is ".$user->getType());
        }

        $title = $this->translator->trans('authorization.removed');
        //task RT-266
        $desc = $this->translator->trans(
            'authorization.description.removed',
            array(
                '%%HOME_PAGE%%' => $this->router->generate('tenant_homepage')
            )
        );
        $scores = $user->getScores();
        foreach ($scores as $score) {
            $this->em->remove($score);
        }
        $reportsPrequeal = $user->getReportsPrequal();
        foreach ($reportsPrequeal as $report) {
            $this->em->remove($report);
        }
        //will be added new status in future for the tenant tab->payment
        //$user->setIsVerified(UserIsVerified::NONE);
        $user->setPassword($this->encoder->encodePassword($user->getPassword(), $user->getSalt()));
        $this->em->persist($user);
        $this->em->flush();

        $this->session->getFlashBag()->add('message_title', $title);
        $this->session->getFlashBag()->add('message_body', $desc);

        return $user;
    }
}
