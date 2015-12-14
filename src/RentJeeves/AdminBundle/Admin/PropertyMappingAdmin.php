<?php

namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\GroupType;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PropertyMappingAdmin extends Admin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('holding')
            ->add('property')
            ->add('externalPropertyId')
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

    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid->add('externalPropertyId');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $container = $this->getConfigurationPool()->getContainer();

        $request = $container->get('request');

        if ($id = $request->get('id')) {
            $em = $container->get('doctrine.orm.default_entity_manager');
            $propertyMapping = $em->getRepository('RjDataBundle:PropertyMapping')->find($id);
            $holding = ($propertyMapping) ? $propertyMapping->getHolding() : null;
        } else {
            $holding = null;
        }

        $formMapper
            ->add(
                'holding',
                'entity',
                array(
                    'class' => 'DataBundle:Holding',
                    'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('holding')
                                ->innerJoin('holding.groups', 'gr')
                                ->where('gr.type = :typeGroup')
                                ->orderBy('holding.name', 'ASC')
                                ->setParameter('typeGroup', GroupType::RENT);
                    }
                )
            )
            ->add(
                'property',
                'sonata_type_model_reference', // Use a text field by property_id rather than a select drop-down
                [
                    'label' => "Property ID",
                    'model_manager' => $this->getModelManager(),
                    'class' => 'RentJeeves\DataBundle\Entity\Property'
                ]
            )
            ->add('externalPropertyId');

        $self = $this;
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $container = $this->getConfigurationPool()->getContainer();
                $translation = $container->get('translator');
                $em = $container->get('doctrine.orm.default_entity_manager');

                $form = $event->getForm();

                $propertyMapping = $form->getData();

                $existentPropertyMapping = $em->getRepository('RjDataBundle:PropertyMapping')
                    ->findOneBy(
                        array(
                            'property'     => $propertyMapping->getProperty()->getId(),
                            'holding'    => $propertyMapping->getHolding()->getId(),
                        )
                    );

                if ($existentPropertyMapping) {
                    $form->get('externalPropertyId')->addError(
                        new FormError(
                            $translation->trans(
                                'admin.form.error.property_mapping'
                            )
                        )
                    );
                }
            }
        );
    }
}
