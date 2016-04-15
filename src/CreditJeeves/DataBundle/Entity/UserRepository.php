<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class UserRepository extends EntityRepository
{
    /**
     * @param string $userName
     *
     * @return User
     */
    public function findLastByPartOfUserName($userName)
    {
        return $this->createQueryBuilder('u')
            ->where('REGEXP(u.usernameCanonical, :regexp) = true')
            ->setParameter('regexp', sprintf('^%s[[:digit:]]*$', $userName))
            ->orderBy('u.usernameCanonical', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
