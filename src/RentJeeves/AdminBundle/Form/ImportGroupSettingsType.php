<?php
namespace RentJeeves\AdminBundle\Form;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service("form.import_group_settings")
 */
class ImportGroupSettingsType extends Base
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
            'source',
            'choice',
            [
                'error_bubbling' => true,
                'choices' => ImportSource::cachedTitles(),
                'required' => true,
                'expanded' => true,
            ]
        );

        $builder->add(
            'importType',
            'choice',
            [
                'error_bubbling' => true,
                'choices' => ImportType::cachedTitles(),
                'required' => true,
            ]
        );

        $builder->add(
            'csvFieldDelimiter',
            'text',
            [
                'error_bubbling' => true,
                'data' => ',',
                'required' => false,
            ]
        );

        $builder->add(
            'csvTextDelimiter',
            'text',
            [
                'error_bubbling' => true,
                'data' => '"',
                'required' => false,
            ]
        );

        $builder->add(
            'csvDateFormat',
            'choice',
            [
                'error_bubbling' => true,
                'choices' => ImportMapping::$mappingDates,
                'required' => false,
            ]
        );

        $builder->add(
            'apiPropertyIds',
            'text',
            [
                'error_bubbling' => true,
                'required' => false,
            ]
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation'    => true,
            'data_class'            => 'RentJeeves\DataBundle\Entity\ImportGroupSettings',
        ]);
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_import_group_settings';
    }
}
