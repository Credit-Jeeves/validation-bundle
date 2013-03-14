<?php
namespace CreditJeeves\ComponentBundle\Menu;

use Symfony\Component\Security\Core\SecurityContext;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
abstract class Base
{
    protected $items = array();

    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @param SecurityContext $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @return \CreditJeeves\UserBundle\Entity\User
     */
    protected function getUser()
    {
        return $this->securityContext->getToken()->getUser();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
