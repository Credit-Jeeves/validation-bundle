<?php

namespace RentJeeves\AdminBundle\Admin;

use Knp\Menu\ItemInterface;
use Rj\EmailBundle\Admin\EmailTemplateAdmin as BaseAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class EmailTemplateAdmin extends BaseAdmin
{
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->andWhere($alias . '.name LIKE :prefix');
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

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('To email (only test)')
            ->add('translationProxies_testEmailTo', 'email', [
                'label' => 'Email to',
                'required' => false,
                'property_path' => sprintf('translationProxies[%s].testEmailTo', $this->locales[0]),
            ])
            ->end()
            ->with('Test variables')
            ->add('translationProxies_testVariables', 'textarea', [
                'label' => 'Variables',
                'required' => false,
                'property_path' => sprintf('translationProxies[%s].testVariables', $this->locales[0]),
                'attr' => [
                    'title' => 'You can use the variables to change design:
                    logoName, partnerName, partnerAddress, loginUrl, isPoweredBy'
                ]
            ])
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(ItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        return null;
    }
}
