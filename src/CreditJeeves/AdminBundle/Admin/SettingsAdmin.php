<?php

namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;

class SettingsAdmin extends Admin
{
    const TYPE = 'settings';

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_' . self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/' . self::TYPE;
    }

    /**
     * @param RouteCollection $collection
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('export');
        $collection->remove('list');
        $collection->remove('create');
    }

    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Net Connect')
            ->add('precise_id_user_pwd', 'text')
            ->add('precise_id_eai', 'text')
            ->add('credit_profile_user_pwd', 'text')
            ->add('credit_profile_eai', 'text')
            ->end()
            ->with('Other')
            ->add('rights')
            ->add('scoretrackFreeUntil')
            ->add('loginMessage', 'textarea', ['required' => false])
            ->end();
    }
}
