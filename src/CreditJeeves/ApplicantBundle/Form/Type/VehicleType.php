<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\DataBundle\Utility\VehicleUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VehicleType extends AbstractType
{
    protected $mark;

    protected $model;

    public function __construct(array $vehiclesFull)
    {
        $this->mark = array_keys($vehiclesFull);
        $this->model = array_values($vehiclesFull);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'make',
            'choice',
            array(
                'choices' => $this->mark,
                'data'    => 0,
            )
        );
        $builder->add(
            'model',
            'choice',
            array(
                'choices' =>  array_keys($this->model[0]),
            )
        );

        $self = $this;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($self) {
                $form = $event->getForm();
                $data = $event->getData();
                if (!isset($data['make'])) {
                    return;
                }

                $make = $data['make'];

                $form->add(
                    'model',
                    'choice',
                    array(
                        'choices' => (isset($self->model[$make]))? array_keys($self->model[$make]): array(),
                    )
                );
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'intention'          => 'username'
            )
        );
    }
    
    public function getName()
    {
        return 'creditjeeves_corebundle_vehicletype';
    }
}
