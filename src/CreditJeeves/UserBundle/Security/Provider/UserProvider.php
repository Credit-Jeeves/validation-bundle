<?php
namespace CreditJeeves\UserBundle\Security\Provider;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider implements UserProviderInterface
{
  private $userManager;

  public function __construct(UserManagerInterface $userManager)
  {
    $this->userManager = $userManager;
  }

  public function loadUserByUsername($username)
  {
    $user = $this->userManager->findUserByEmail($username);

    if (!$user) {
      throw new UsernameNotFoundException(sprintf('No user with email "%s" was found.', $username));
    }

    return $user;
  }

  public function refreshUser(UserInterface $user)
  {
    return $this->userManager->refreshUser($user);
  }

  public function supportsClass($class)
  {
    return $this->userManager->supportsClass($class);
  }
}