<?php

namespace CreditJeeves\ApplicantBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use \DateTime;
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

    protected $helpLink;

    /**
     * @InjectParams({
     *     "em"             = @Inject("doctrine.orm.entity_manager"),
     *     "translator"     = @Inject("translator"),
     *     "encoder"        = @Inject("user.security.encoder.digest"),
     *     "session"        = @Inject("session"),
     *     "externalUrls"   = @Inject("%external_urls%")
     * })
     */
    public function __construct($em, $translator, $encoder, $session, $externalUrls)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->helpLink = $externalUrls['user_voice'];
        $this->encoder = $encoder;
        $this->session = $session;
    }

    public function remove(UserInterface $user)
    {
        if ($user->getType() !== UserType::APPLICANT) {
            throw new LogicException("We can remove user with type applicant, but this user is ".$user->getType());
        }

        $desc = $this->translator->trans(
            'authorization.description.removed',
            array(
                '%%FEEDBACK_PAGE%%' => $this->helpLink
            )
        );
        $title = $this->translator->trans('authorization.removed');

        $newUser = $user->getUserToRemove();
        $newUser->setPassword($this->encoder->encodePassword($user->getPassword(), $user->getSalt()));
        $this->em->transactional(
            function ($em) use ($user, $newUser) {
                $em->remove($user);
                $em->flush();
                $newUser->setLastLogin(new DateTime());
                $em->persist($newUser);
            }
        );

        $this->session->invalidate();

        $this->session->getFlashBag()->add('message_title', $title);
        $this->session->getFlashBag()->add('message_body', $desc);

        return $user;
    }
}