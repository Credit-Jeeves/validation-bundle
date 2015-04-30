<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;
use RentJeeves\LandlordBundle\Form\ContractType;
use RentJeeves\LandlordBundle\Form\InviteTenantType;

class InviteTenantContractType extends AbstractType
{

    protected $user;
    protected $group;

    public function __construct($user, $group = null)
    {
        $this->user = $user;
        $this->group = $group;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'tenant',
            new InviteTenantType()
        );
        $builder->add(
            'contract',
            new ContractType($this->group)
        );
        if ($this->group && $this->group->getGroupSettings()->getIsIntegrated()) {
            $builder->add(
                'resident',
                new TenantResidentMappingType()
            );
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
                'validation_groups' => array(
                    'tenant_invite',
                ),
                'error_mapping' => array(
                    'addressValid' => 'address'
                ),
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_landlordbundle_invitetenantcontracttype';
    }
}
