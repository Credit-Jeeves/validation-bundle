<?php

namespace RentJeeves\PublicBundle\Form;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Validators\TenantEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;

class TenantType extends AbstractType
{
    protected $em;

    /**
     * @var ContractWaiting
     */
    protected $waitingContract = null;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em = null)
    {
        $this->em = $em;
    }

    /**
     * @param ContractWaiting $waitingContract
     */
    public function setWaitingContract(ContractWaiting $waitingContract)
    {
        $this->waitingContract = $waitingContract;
    }

    /**
     * @return ContractWaiting
     */
    public function getWaitingContract()
    {
        return $this->waitingContract;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            null,
            array(
                'label' => 'Name*',
            )
        );
        $builder->add('last_name');

        $emailOptions = array(
            'label' => 'Email*',
        );
        if ($options['inviteEmail']) {
            $emailOptions['constraints'] =  new TenantEmail(
                array(
                    'groups'    => 'registration_tos'
                )
            );
        }

        $builder->add(
            'email',
            null,
            $emailOptions
        );

        $builder->add(
            $builder->create('phone', 'text', ['required' => false])->addViewTransformer(new PhoneNumberTransformer())
        );
        $builder->add(
            'password',
            'repeated',
            array(
                'first_name'    => 'Password',
                'first_options' => array(
                    'label' => 'Password*'
                 ),
                'second_name'   => 'Verify_Password',
                'second_options' => array(
                    'label' => 'Verify Password*'
                 ),
                'type'          => 'password',
                'mapped'        => false,
                'constraints'   => array(
                    new NotBlank(
                        array(
                            'groups'    => 'password',
                            'message'   => 'error.user.password.empty',
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'tos',
            'checkbox',
            array(
                'label'         => '',
                'data'          => false,
                'mapped'        => false,
                'constraints'    => new True(
                    array(
                        'message'   => 'error.user.tos',
                        'groups'    => 'registration_tos'
                    )
                ),
            )
        );

        /**
         * If we don't have entity manager it's means it's sub-form and we don't need field below
         */
        if (is_null($this->em)) {
            return;
        }

        $builder->add(
            'propertyId',
            'hidden',
            array(
                'label'             => '',
                'data'              => false,
                'mapped'            => false,
                'error_bubbling'    => true,
                'constraints'       => new NotBlank(
                    array(
                        'message' => 'error.property.empty',
                        'groups' => 'registration_tos'
                    )
                ),
            )
        );

        $builder->add(
            'unit',
            new UnitType(),
            array(
                'mapped'         => false,
                'error_bubbling' => true,
            )
        );

        $self = $this;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($self) {
                $data = $event->getData();
                $form = $event->getForm();
                $propertyId = $data['propertyId'];
                if (empty($propertyId)) {
                    return;
                }
                /**
                 * @var $property Property
                 */
                $property = $self->em->getRepository('RjDataBundle:Property')->find($propertyId);
                if ($property && $property->getPropertyAddress()->isSingle()) {
                    $unit = $property->getExistingSingleUnit();
                    $form->get('unit')->setData($unit);
                }
            }
        );

        /**
         * Logic which we must have in this event describe here
         * https://credit.atlassian.net/wiki/display/RT/Tenant+Sign+up+for+Integrated+Property
         */
        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($options, $self) {
                $form = $event->getForm();

                $propertyId = $form->get('propertyId')->getData();
                if (empty($propertyId)) {
                    return;
                }
                /**
                 * @var $property Property
                 */
                $property = $self->em->getRepository('RjDataBundle:Property')->find($propertyId);

                if (empty($property)) {
                    $form->addError(new FormError('error.property.empty'));

                    return;
                }
                if ($property->getPropertyAddress()->isSingle()) {
                    $unit = $property->getExistingSingleUnit();
                    if (!$property->hasIntegratedGroup()) {
                        return;
                    }
                } else {
                    /**
                     * @var $formUnit Unit
                     */
                    $formUnit = $form->get('unit')->getData();
                    $unitName = $formUnit->getName();
                    if (empty($unitName)) {
                        return;
                    }
                    /**
                     * @var $unit Unit
                     */
                    $unit = $property->searchUnit($unitName);

                    if (empty($unit)) {
                        return;
                    }
                    /**
                     * @var $group Group
                     */
                    $group = $unit->getGroup();
                    $isIntegratedUnit = $group->getGroupSettings()->getIsIntegrated();
                    if (!$isIntegratedUnit) {
                        return;
                    }
                }

                $contractsWaiting = $unit->getContractsWaiting();

                /**
                 * @var $tenant Tenant
                 */
                $tenant = $form->getData();
                $firstName = strtolower($tenant->getFirstName());
                $lastName = strtolower($tenant->getLastName());

                /**
                 * @var $contractWaiting ContractWaiting
                 */
                foreach ($contractsWaiting as $contractWaiting) {
                    if (strtolower($contractWaiting->getFirstName()) !== $firstName) {
                        continue;
                    }

                    if (strtolower($contractWaiting->getLastName()) !== $lastName) {
                        continue;
                    }

                    $self->setWaitingContract($contractWaiting);
                    break;
                }

                /**
                 * Seems we have waiting contract for this unit and first_name, last_name not the same.
                 * Create a contract if group is integrated and pay_anything is allowed.
                 * Otherwise block with error.
                 */
                if (is_null($self->getWaitingContract()) && count($contractsWaiting) > 0) {
                    $isAllowedPayAnything = $unit->getGroup()->getGroupSettings()->isAllowPayAnything();
                    $isIntegrated = $unit->getGroup()->getGroupSettings()->getIsIntegrated();
                    if ($isIntegrated && $isAllowedPayAnything) {
                        $contractWaiting = $contractsWaiting->first();
                        $contractWaiting->setStatus(ContractStatus::PENDING);
                        $self->setWaitingContract($contractWaiting);
                    } else {
                        $form->addError(new FormError('error.unit.reserved'));
                    }
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'RentJeeves\DataBundle\Entity\Tenant',
                'validation_groups'  => array(
                    'registration_tos',
                    'invite',
                    'password'
                ),
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
                'inviteEmail'        => true
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_publicbundle_tenanttype';
    }
}
