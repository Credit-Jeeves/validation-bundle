<?php

namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
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
        $query->andWhere($alias.'.name NOT LIKE :prefix');
        $query->setParameter('prefix', 'rj%');
        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        ->addIdentifier('name')
        ->addIdentifier('createdAt')
        ->addIdentifier('updatedAt')
        ->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            )
        );
    }
}
