<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

class UserRepository extends EntityRepository
{
    /**
     * @param string $userName
     *
     * @return string|null
     */
    public function getLastUserNameByPartOfUserName($userName)
    {
        $lastUserName = $this->_em->getConnection()->query(
            "SELECT u.username_canonical
             FROM cj_user u
             WHERE ((u.username_canonical REGEXP \"^{$userName}[[:digit:]]*$\") = 1)
             ORDER BY LENGTH(u.username_canonical) DESC, u.username_canonical DESC"
        )->fetchColumn(0);

        return $lastUserName ?: null;
    }
}
