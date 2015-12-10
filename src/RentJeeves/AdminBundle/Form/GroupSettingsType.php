<?php
namespace RentJeeves\AdminBundle\Form;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\TypeDebitFee;
use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RentJeeves\DataBundle\Entity\GroupSettings as GroupSetting;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service("form.group_settings")
 */
class GroupSettingsType extends Base
{
    protected $translator;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @InjectParams({
     *     "em"             = @Inject("doctrine.orm.entity_manager"),
     *     "translator"     = @Inject("translator")
     * })
     */
    public function __construct(EntityManager $em, $translator)
    {
        $this->translator = $translator;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'isPidVerificationSkipped',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'is.pid.verification.skip',
                'required'          => false,
            )
        );

        $builder->add(
            'isIntegrated',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'is.integrated',
                'required'          => false,
            )
        );

        $builder->add(
            'isReportingOff',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'is.reporting_off',
                'required'          => false,
            )
        );

        $builder->add(
            'payBalanceOnly',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'pay.balance.only',
                'required'          => false,
            )
        );

        $builder->add(
            'showPropertiesTab',
            'checkbox',
            [
                'error_bubbling'    => true,
                'required'          => false,
            ]
        );

        $builder->add(
            'allowPayAnything',
            'checkbox',
            [
                'error_bubbling'    => true,
                'required'          => false,
            ]
        );

        $dueDate = array();
        foreach (range(1, 31, 1) as $key => $value) {
            $dueDate[$value] = $value;
        }

        $builder->add(
            'dueDate',
            'choice',
            array(
                'choices'           => $dueDate,
                'error_bubbling'    => true,
                'label'             => 'common.default.due_date',
                'required'          => true,
                'empty_data'        => 1,
            )
        );

        $builder->add(
            'openDate',
            'choice',
            array(
                'choices'           => $dueDate,
                'error_bubbling'    => true,
                'label'             => 'common.default.open_date',
                'required'          => true,
                'empty_data'        => 1,
            )
        );

        $builder->add(
            'closeDate',
            'choice',
            array(
                'choices'           => $dueDate,
                'error_bubbling'    => true,
                'label'             => 'common.default.close_date',
                'required'          => true,
                'empty_data'        => 31,
            )
        );

        $builder->add('paymentProcessor', 'choice', ['choices' => PaymentProcessor::cachedTitles()]);

        $builder->add(
            'feeCC',
            'number',
            ['label' => 'CC Fee (%)', 'required' => false]
        );
        $builder->add(
            'feeACH',
            'number',
            ['label' => 'ACH Fee ($)', 'required' => false]
        );
        $builder->add(
            'passedAch',
            'checkbox',
            ['label' => 'Is passed ach', 'required' => false]
        );
        $builder->add(
            'autoApproveContracts',
            'checkbox',
            ['label' => 'Is Auto-Approve Contracts', 'required' => false]
        );

        $builder->add(
            'showRentOnDashboard',
            'checkbox',
            ['label' => 'admin.group_settings.label.show_rent_dashboard', 'required' => false]
        );

        $builder->add(
            'showRentOnWizard',
            'checkbox',
            ['label' => 'admin.group_settings.label.show_rent_wizard', 'required' => false]
        );

        $builder->add(
            'allowedDebitFee',
            'checkbox',
            ['label' => 'Is Allowed Debit Fee?', 'required' => false]
        );

        $builder->add(
            'typeDebitFee',
            'choice',
            ['choices' => TypeDebitFee::cachedTitles(), 'required' => false]
        );

        $builder->add(
            'debitFee',
            'number',
            ['required' => false]
        );

        $builder->add(
            'externalResidentFollowsUnit',
            'checkbox',
            ['label' => 'External Resident Follows Unit?', 'required' => false]
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                /**
                 * @var $groupSettings GroupSetting
                 */
                $groupSettings = $event->getData();
                if (!$groupSettings->getIsIntegrated() && $groupSettings->getPayBalanceOnly()) {
                    $form->get('payBalanceOnly')->addError(
                        new FormError(
                            $this->translator->trans('pay.balance.only.error')
                        )
                    );
                }

                $hasReccuringPayment = $this->em->getRepository('RjDataBundle:GroupSettings')->hasReccuringPayment(
                    $groupSettings->getId()
                );

                if ($hasReccuringPayment && $groupSettings->getPayBalanceOnly()) {
                    $form->get('payBalanceOnly')->addError(
                        new FormError(
                            $this->translator->trans('pay.balance.only.reccuring_error')
                        )
                    );
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\GroupSettings',
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_group_settings';
    }
}
