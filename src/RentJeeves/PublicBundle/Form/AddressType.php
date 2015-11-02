<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'unit',
            null
        );

        $builder->add(
            'street',
            null
        );

        $builder->add(
            'zip',
            null
        );

        $builder->add(
            'city',
            null
        );

        $builder->add(
            'area',
            'choice',
            array(
                'choices'   => $this->getListOfStates(),
                'required'  => true,
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\MailingAddress',
                'validation_groups' => array(
                    'user_address_new',
                ),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'landlordType';
    }

    protected function getListOfStates()
    {
        $stateList = array(
            'AL'=>"AL",
            'AK'=>"AK",
            'AZ'=>"AZ",
            'AR'=>"AR",
            'CA'=>"CA",
            'CO'=>"CO",
            'CT'=>"CT",
            'DE'=>"DE",
            'DC'=>"DC",
            'FL'=>"FL",
            'GA'=>"GA",
            'HI'=>"HI",
            'ID'=>"ID",
            'IL'=>"IL",
            'IN'=>"IN",
            'IA'=>"IA",
            'KS'=>"KS",
            'KY'=>"KY",
            'LA'=>"LA",
            'ME'=>"ME",
            'MD'=>"MD",
            'MA'=>"MA",
            'MI'=>"MI",
            'MN'=>"MN",
            'MS'=>"MS",
            'MO'=>"MO",
            'MT'=>'MT',
            'NE'=>'NE',
            'NV'=>'NV',
            'NH'=>'NH',
            'NJ'=>'NJ',
            'NM'=>'NM',
            'NY'=>'NY',
            'NC'=>'NC',
            'ND'=>'ND',
            'OH'=>'OH',
            'OK'=>'OK',
            'OR'=>'OR',
            'PA'=>'PA',
            'RI'=>'RI',
            'SC'=>'SC',
            'SD'=>'SD',
            'TN'=>'TN',
            'TX'=>'TX',
            'UT'=>'UT',
            'VT'=>'VT',
            'VA'=>'VA',
            'WA'=>'WA',
            'WV'=>'WV',
            'WI'=>'WI',
            'WY'=>'WY',
        );

        return $stateList;
    }
}
