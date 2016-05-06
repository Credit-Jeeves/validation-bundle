<?php

namespace RentJeeves\LandlordBundle\Form;

use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\Translator;

/**
 * This form for new Tenant
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportTenantType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param EntityManager $em
     * @param Translator    $translator
     */
    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            'text'
        );


        $builder->add(
            'last_name',
            'text'
        );

        $builder->add(
            'email',
            'text'
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $email = $form->get('email')->getViewData();
                if (empty($email)) {
                    return;
                }

                $user = $this->em->getRepository('DataBundle:User')->findOneBy(
                    ['email' => $email]
                );

                if ($user && $user->getType() !== UserType::TENANT) {
                    $form->get('email')->addError(
                        new FormError($this->translator->trans('import.error.email_used_by_different_user_type'))
                    );
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
                'validation_groups' => array(
                    'import',
                ),
                'csrf_protection'    => false,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_new_tenant';
    }
}
