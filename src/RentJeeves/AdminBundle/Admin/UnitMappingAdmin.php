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

class UnitMappingAdmin extends Admin
{
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('unit')
            ->add('externalUnitId')
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
        $datagrid->add('externalUnitId');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add(
                'unit',
                'sonata_type_model_reference', // Use a text field by rj_unit.id rather than a welect drop-down
                array(
                    'label' => "RentTrack Unit ID",
                    'model_manager' => $this->getModelManager(),
                    'class' => 'RentJeeves\DataBundle\Entity\Unit'
                )
            )
            ->add('externalUnitId');

        $self = $this;
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $container = $this->getConfigurationPool()->getContainer();
                $translation = $container->get('translator');
                $em = $container->get('doctrine.orm.default_entity_manager');

                $form = $event->getForm();

                $unitMapping = $form->getData();

                $existingUnitMapping = $em->getRepository('RjDataBundle:UnitMapping')
                    ->findOneBy(
                        array(
                            'unit'     => $unitMapping->getUnit()->getId(),
                            'externalUnitId'    => $unitMapping->getExternalUnitId(),
                        )
                    );

                $existUnitDifferentMap = $em->getRepository('RjDataBundle:UnitMapping')
                    ->findOneBy(
                        array(
                            'unit'     => $unitMapping->getUnit()->getId(),
                        )
                    );

                if ($existingUnitMapping) {
                    $form->get('externalUnitId')->addError(
                        new FormError(
                            $translation->trans(
                                'admin.form.error.unit_mapping'
                            )
                        )
                    );
                } elseif ($existUnitDifferentMap) {
                    $form->get('externalUnitId')->addError(
                        new FormError(
                            $translation->trans(
                                'admin.form.error.unit_mapping.unit_exist'
                            )
                        )
                    );
                }
            }
        );
    }
}
