<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\GroupType;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ResidentMappingAdmin extends Admin
{
    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid
            ->add('holding')
            ->add('tenant')
            ->add('residentId');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('holding')
            ->add('tenant')
            ->add('residentId')
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

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('holding')
            ->add('tenant')
            ->add('residentId');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
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
                'tenant',
                'entity',
                array(
                    'class' => 'RjDataBundle:Tenant',
                    'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('tenant')
                                ->orderBy('tenant.last_name', 'ASC');
                        }
                )
            )
            ->add('residentId');

        $self = $this;
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $container = $this->getConfigurationPool()->getContainer();
                $translation = $container->get('translator');
                $em = $container->get('doctrine.orm.default_entity_manager');

                $form = $event->getForm();
                /**
                 * @var $residentMapping ResidentMapping
                 */
                $residentMapping = $form->getData();

                $residentMappingDuplicate = $em->getRepository('RjDataBundle:ResidentMapping')
                    ->findOneBy(
                        array(
                            'residentId' => $residentMapping->getResidentId(),
                            'tenant'     => $residentMapping->getTenant()->getId(),
                            'holding'    => $residentMapping->getHolding()->getId(),
                        )
                    );

                if ($residentMappingDuplicate) {
                    $form->get('residentId')->addError(
                        new FormError(
                            $translation->trans(
                                'admin.form.contract_waiting.error.residentId'
                            )
                        )
                    );
                }
            }
        );
    }
}
