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
use Symfony\Component\HttpFoundation\Request;

class ResidentMappingAdmin extends Admin
{
    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid
            ->add('residentId')
            ->add('tenant.email')
            ->add('tenant.first_name')
            ->add('tenant.last_name')
            ->add('holding.name');
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
        $container = $this->getConfigurationPool()->getContainer();
        /**
         * @var $request Request
         */
        $request = $container->get('request');
        $params = $request->request->all();
        $uniqid = $request->query->get('uniqid');

        if (isset($params[$uniqid]['holding'])) {
            $holding = $params[$uniqid]['holding'];
            $tenant = $params[$uniqid]['tenant'];
        } elseif ($id = $request->get('id')) {
            $em = $container->get('doctrine.orm.default_entity_manager');
            $residentMapping = $em->getRepository('RjDataBundle:ResidentMapping')->find($id);
            $holding = ($residentMapping)? $residentMapping->getHolding()->getId() : null;
            $tenant = $residentMapping ? $residentMapping->getTenant()->getId() : null;
        } else {
            $holding = null;
            $tenant = null;
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
                'tenant',
                'entity',
                array(
                    'class' => 'RjDataBundle:Tenant',
                    'query_builder' => function (EntityRepository $er) use ($tenant, $holding) {
                            // TO AVOID MEMORY EXHAUSTION caused by doctrine hydration:
                            // All tenants are loaded by ajax, so we can load only one tenant here.
                            // This query is also used inside doctrine to load corresponding entity
                            // for chosen tenant on the form (there tenant is just id).
                            return $er->getTenantByIdOrByHolding($tenant, $holding);
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
