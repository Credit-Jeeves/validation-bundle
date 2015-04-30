<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\GroupType;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email;
use RentJeeves\CoreBundle\Services\ContractProcess;
use Exception;

class ContractWaitingAdmin extends Admin
{

    protected $email = '';

    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid
            ->add('rent')
            ->add('firstName')
            ->add('lastName')
            ->add('residentId')
            ->add('integratedBalance');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('group')
            ->add('property')
            ->add('unit')
            ->add('rent')
            ->add('firstName')
            ->add('lastName')
            ->add('residentId')
            ->add('integratedBalance')
            ->add('startAt')
            ->add('finishAt')
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
            ->add('group')
            ->add('property')
            ->add('unit')
            ->add('rent')
            ->add('firstName')
            ->add('lastName')
            ->add('residentId')
            ->add('integratedBalance')
            ->add('startAt')
            ->add('finishAt');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $request = $container->get('request');
        $uniqueId = $request->query->get('uniqid');
        $params = $request->request->all();

        /** @var ContractWaiting $contractWaiting */
        $contractWaiting = $this->getSubject();
        $group = $contractWaiting->getGroup();
        $property = $contractWaiting->getProperty();

        if (isset($params[$uniqueId])) {
            $group = $params[$uniqueId]['group'];
            $property = $params[$uniqueId]['property'];
        }

        $formMapper
            ->add(
                'group',
                'entity',
                array(
                    'class' => 'DataBundle:Group',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('gr')
                            ->where('gr.type = :typeGroup')
                            ->orderBy('gr.name', 'ASC')
                            ->setParameter('typeGroup', GroupType::RENT);
                    }
                )
            )
            ->add(
                'property',
                'entity',
                array(
                    'class' => 'RjDataBundle:Property',
                    'required' => true,
                    'query_builder' => function (EntityRepository $er) use ($group) {
                        return $er->createQueryBuilder('pr')
                            ->innerJoin('pr.property_groups', 'gr')
                            ->where('gr.id = :group')
                            ->setParameter('group', $group);
                    }
                )
            )
            ->add(
                'unit',
                'entity',
                array(
                    'class' => 'RjDataBundle:Unit',
                    'required' => true,
                    'query_builder' => function (EntityRepository $er) use ($group, $property) {
                        return $er->createQueryBuilder('u')
                            ->where('u.property = :property')
                            ->andWhere('u.group = :group')
                            ->setParameter('group', $group)
                            ->setParameter('property', $property);
                    }
                )
            )
            ->add('rent')
            ->add('residentId')
            ->add(
                'email',
                'text',
                array(
                    'mapped' => false,
                    'required' => false,
                    'constraints' => array(
                        new Email(),
                    )
                )
            )
            ->add('firstName')
            ->add('lastName')
            ->add('integratedBalance')
            ->add('startAt')
            ->add('finishAt');

        $self = $this;
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $container = $this->getConfigurationPool()->getContainer();
                $translation = $container->get('translator');
                $em = $container->get('doctrine.orm.default_entity_manager');

                $form = $event->getForm();
                $self->email = $form->get('email')->getNormData();
                /**
                 * @var $contractWaiting ContractWaiting
                 */
                $contractWaiting = $form->getData();
                $group = $contractWaiting->getGroup();
                $property = $contractWaiting->getProperty();
                $unit = $contractWaiting->getUnit();

                $residentMappingDuplicate = $em->getRepository('RjDataBundle:ResidentMapping')
                    ->checkResidentMappingDuplicate(
                        $group->getHolding(),
                        $contractWaiting->getResidentId(),
                        $self->email
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

                if (!$group->getGroupProperties()->contains($property)) {
                    $form->get('property')->addError(
                        new FormError(
                            $translation->trans(
                                'admin.form.contract_waiting.error.property'
                            )
                        )
                    );
                }

                if ($property && $unit && !$property->getUnits()->contains($unit)) {
                    $form->get('unit')->addError(
                        new FormError(
                            $translation->trans('admin.form.contract_waiting.error.unit')
                        )
                    );
                }
            }
        );
    }

    /**
     * @param ContractWaiting $contractWaiting
     */
    public function postPersist($contractWaiting)
    {
        $this->moveContract($contractWaiting);
    }

    /**
     * @param ContractWaiting $contractWaiting
     */
    public function postUpdate($contractWaiting)
    {
        $this->moveContract($contractWaiting);
    }

    /**
     * @param ContractWaiting $contractWaiting
     */
    public function moveContract(ContractWaiting $contractWaiting)
    {
        if (empty($this->email)) {
            return;
        }

        $container = $this->getConfigurationPool()->getContainer();
        $mailer = $container->get('project.mailer');
        $em = $container->get('doctrine.orm.default_entity_manager');

        $tenant = $em->getRepository("RjDataBundle:Tenant")->findOneBy(
            array(
                'email' => $this->email,
            )
        );
        /**
         * @var $process ContractProcess
         */
        $process = $container->get("contract.process");

        if (!$tenant) {
            $tenant = new Tenant();
            $tenant->setFirstName($contractWaiting->getFirstName());
            $tenant->setLastName($contractWaiting->getLastName());
            $tenant->setEmail($this->email);
            $tenant->setEmailCanonical($this->email);
            $tenant->setPassword(md5(md5(1)));
            $tenant->setCulture($container->getParameter('kernel.default_locale'));
            $em->persist($tenant);
        }

        $contract = $process->createContractFromWaiting(
            $tenant,
            $contractWaiting
        );
        $contract->setStatus(ContractStatus::INVITE);
        $em->persist($contract);
        $em->remove($contractWaiting);
        $em->flush();

        $landlord = $contract->getGroup()->getHolding()->getLandlords()->first();

        if (empty($landlord)) {
            throw new Exception("We can't find landlord for group with id " . $contract->getGroup()->getId());
        }

        $mailer->sendRjTenantInvite(
            $tenant,
            $landlord,
            $contract,
            $isImported = "1"
        );
    }
}
