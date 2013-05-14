<?php

namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Rj\EmailBundle\Form\Type\CallbackType;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Rj\EmailBundle\Admin\EmailTemplateAdmin as BaseAdmin;

class EmailTemplateAdmin extends BaseAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Email Templates')
            ->add('name')
            ->end();

        $locales = $this->locales;

        foreach ($locales as $locale) {
            $formMapper
                ->with(sprintf("Subject", $locale))
                ->add(
                    sprintf("translationProxies_%s_subject", $locale),
                    'text',
                    array(
                        'label' => $locale,
                        'property_path' => sprintf('translationProxies[%s].subject', $locale),
                        )
                )
                ->end();
        }

        foreach ($locales as $locale) {
            $formMapper
                ->with(sprintf("Body", $locale))
                    ->add(
                        sprintf("translationProxies_%s_body", $locale),
                        'textarea',
                        array(
                            'label' => $locale,
                            'property_path' => sprintf('translationProxies[%s].body', $locale),
                        )
                    )
                ->end();
        }
    }
}
