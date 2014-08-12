<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\GroupType;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email;

class ContractWaitingAdmin extends Admin
{

    protected $email;

    protected function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid
            ->add('rent')
            ->add('firstName')
            ->add('lastName')
            ->add('residentId')
            ->add('integratedBalance')
        ;
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
                        'edit' => array(),                        ),
                        'delete' => array(),
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
        $formMapper
            ->add('group',
                'entity',
                array(
                    'class' => 'DataBundle:Group',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('gr')
                            ->where('gr.type = :typeGroup')
                            ->orderBy('gr.id', 'ASC')
                            ->setParameter('typeGroup', GroupType::RENT)
                            ;
                    }
                )
            )
            ->add('property')
            ->add('unit')
            ->add('rent')
            ->add('residentId')
            ->add('email',
                'text',
                array(
                    'mapped'    => false,
                    'required'  => false,
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
        //@todo add event for pre set data for is single
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $container = $this->getConfigurationPool()->getContainer();
                $translation = $container->get('translator');

                $form = $event->getForm();
                $self->email = $form->get('email')->getNormData();
                /**
                 * @var $contractWaiting ContractWaiting
                 */
                $contractWaiting = $form->getData();
                $group = $contractWaiting->getGroup();
                $property = $contractWaiting->getProperty();
                $unit = $contractWaiting->getUnit();

                if (!$group->getGroupProperties()->contains($property)) {
                    $form->get('property')->addError(
                        new FormError(
                            $self->translator->trans(
                                'admin.form.contract_waiting.error.property'
                            )
                        )
                    );
                }

                if (!$property->getIsSingle() && !$property->getUnits()->contains($unit)) {
                    $form->get('unit')->addError(
                        new FormError(
                            $self->translator->trans('admin.form.contract_waiting.error.unit')
                        )
                    );
                }
            }
        );
    }

    /**
     * @param ContractWaiting $contractWaiting
     */
    public function postUpdate($contractWaiting)
    {
        if (!$this->email) {
            return;
        }

        $container = $this->getConfigurationPool()->getContainer();
    }
}
