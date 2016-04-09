<?php

namespace RentJeeves\PublicBundle\Form;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Validators\TenantEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;
use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;

class TenantType extends AbstractType
{
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em = null)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilder $builder
     * @param array $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('first_name', null, ['label' => 'Name*']);

        $builder->add('last_name');

        $emailOptions = ['label' => 'Email*'];
        if ($options['inviteEmail']) {
            $emailOptions['constraints'] =  new TenantEmail(['groups' => 'registration_tos']);
        }
        $builder->add('email', null, $emailOptions);

        $builder->add(
            $builder->create('phone', 'text', ['required' => false])->addViewTransformer(new PhoneNumberTransformer())
        );

        $builder->add(
            'password',
            'repeated',
            [
                'first_name'     => 'Password',
                'first_options'  => [
                    'label' => 'Password*'
                 ],
                'second_name'    => 'Verify_Password',
                'second_options' => [
                    'label' => 'Verify Password*'
                 ],
                'type'          => 'password',
                'mapped'        => false,
                'constraints'   => new NotBlank(
                    [
                        'groups'  => 'password',
                        'message' => 'error.user.password.empty',
                    ]
                ),
            ]
        );

        $builder->add(
            'tos',
            'checkbox',
            [
                'label'         => '',
                'data'          => false,
                'mapped'        => false,
                'constraints'   => new True(
                    [
                        'message' => 'error.user.tos',
                        'groups'  => 'registration_tos'
                    ]
                ),
            ]
        );

        /**
         * If we don't have entity manager it's means it's sub-form and we don't need field below
         */
        if (is_null($this->em)) {
            return;
        }

        $builder->add(
            'propertyId',
            'entity_hidden',
            [
                'label'             => '',
                'mapped'            => false,
                'error_bubbling'    => true,
                'constraints'       => new NotBlank(
                    [
                        'message' => 'error.property.empty',
                        'groups'  => 'registration_tos'
                    ]
                ),
                'class' => 'RentJeeves\DataBundle\Entity\Property',
            ]
        );

        $builder->add(
            'unit',
            new UnitType(),
            [
                'mapped'         => false,
                'error_bubbling' => true,
            ]
        );

        $builder->add(
            'contractWaiting',
            'entity_hidden',
            [
                'mapped' =>false,
                'class' => 'RentJeeves\DataBundle\Entity\ContractWaiting',
            ]
        );

        /**
         * Logic which we must have in this event describe here
         * https://credit.atlassian.net/wiki/display/RT/Tenant+Sign+up+for+Integrated+Property
         */
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                $propertyId = $data['propertyId'];
                if (empty($propertyId)) {
                    return;
                }

                /** @var Property $property */
                $property = $this->em->getRepository('RjDataBundle:Property')->find($propertyId);
                if (empty($property)) {
                    $form->addError(new FormError('error.property.empty'));

                    return;
                }

                if ($property && $property->getPropertyAddress()->isSingle()) {
                    $unit = $property->getExistingSingleUnit();
                    $form->get('unit')->setData($unit);
                    if (!$property->hasIntegratedGroup()) {
                        return;
                    }
                } else {
                    if (empty($data['unit']['name']) or
                        !$unit = $property->searchUnit($data['unit']['name']) or
                        !$unit->getGroup()->getGroupSettings()->getIsIntegrated()
                    ) {
                        return;
                    }
                }

                $contractsWaiting = $this->em->getRepository('RjDataBundle:Contract')->getAllWaitingForUnit($unit);
                $firstName = strtolower($data['first_name']);
                $lastName = strtolower($data['last_name']);

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
                    $data['contractWaiting'] = $contractWaiting->getId();
                    break;
                }

                /**
                 * Seems we have waiting contract for this unit and first_name, last_name not the same.
                 * Allow to create a contract if group is integrated and pay_anything is allowed.
                 * Otherwise block with error.
                 */
                if (empty($data['contractWaiting']) &&
                    count($contractsWaiting) > 0 &&
                    !$unit->getGroup()->getGroupSettings()->isAllowPayAnything()
                ) {
                        $form->addError(new FormError('error.unit.reserved'));
                }
                $event->setData($data);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => 'RentJeeves\DataBundle\Entity\Tenant',
            'validation_groups'  => [
                'registration_tos',
                'invite',
                'password'
            ],
            'csrf_protection'    => true,
            'csrf_field_name'    => '_token',
            'cascade_validation' => true,
            'inviteEmail'        => true
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rentjeeves_publicbundle_tenanttype';
    }
}
