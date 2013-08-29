<?php
namespace CreditJeeves\CoreBundle\Security;

use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @~DI\Service("security.context")
 */
class Context extends SecurityContext
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @DI\InjectParams({
     *     "authenticationManager" = @DI\Inject("security.authentication.manager"),
     *     "accessDecisionManager" = @DI\Inject("security.access.decision_manager"),
     *     "alwaysAuthenticate" = @DI\Inject("%security.access.always_authenticate_before_granting%")
     * })
     */
    public function __construct(AuthenticationManagerInterface $authenticationManager,
        AccessDecisionManagerInterface $accessDecisionManager,
        $alwaysAuthenticate = false
    ) {
        parent::__construct($authenticationManager, $accessDecisionManager, $alwaysAuthenticate);
    }

    /**
     * @DI\InjectParams({
     *     "session" = @DI\Inject("session")
     * })
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        $token = parent::getToken();
        if (!is_object($token)) {
            return null;
        }
        $oldUser = $token->getUser();
//var_dump($token->getUser());die('OOOOKK ' . $oldUser->getId());
        if ($this->session->get('observe_admin_id') &&
            ($id = $this->session->get('observe_user_id'))/* &&
            (empty($oldUser) || $oldUser->getId() != $id)*/
        ) {
            $token->setUser($this->em->getRepository('DataBundle:User')->find($id));
        }

        return $token;
    }
}
