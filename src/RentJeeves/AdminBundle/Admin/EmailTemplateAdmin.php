<?php

namespace RentJeeves\AdminBundle\Admin;

use Rj\EmailBundle\Admin\EmailTemplateAdmin as BaseAdmin;

class EmailTemplateAdmin extends BaseAdmin
{
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->andWhere($alias.'.name LIKE :prefix');
        $query->setParameter('prefix', 'rj%');
        return $query;
    }
}
