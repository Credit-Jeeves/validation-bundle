<?php
namespace RentJeeves\AdminBundle\Form;

use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportGroupSettingsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'source',
            'choice',
            [
                'error_bubbling' => true,
                'label' => 'admin.import_group_settings.label.source',
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
                'label' => 'admin.import_group_settings.label.import_type',
                'choices' => [
                    ImportType::SINGLE_PROPERTY => 'import.type.single_property',
                    ImportType::MULTI_PROPERTIES => 'import.type.multi_property',
                    ImportType::MULTI_GROUPS => 'import.type.multi_groups'
                ],
                'required' => true,
            ]
        );

        $builder->add(
            'csvFieldDelimiter',
            'text',
            [
                'error_bubbling' => true,
                'label' => 'admin.import_group_settings.label.csv_field_delimiter',
                'required' => false,
            ]
        );

        $builder->add(
            'csvTextDelimiter',
            'text',
            [
                'error_bubbling' => true,
                'label' => 'admin.import_group_settings.label.csv_text_delimiter',
                'required' => false,
            ]
        );

        $builder->add(
            'csvDateFormat',
            'choice',
            [
                'error_bubbling' => true,
                'label' => 'admin.import_group_settings.label.csv_date_format',
                'choices' => ImportMapping::$mappingDates,
                'required' => false,
            ]
        );

        $builder->add(
            'apiPropertyIds',
            'text',
            [
                'error_bubbling' => true,
                'label' => 'admin.import_group_settings.label.api_property_ids',
                'required' => false,
            ]
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation'    => true,
            'data_class'            => 'RentJeeves\DataBundle\Entity\ImportGroupSettings',
        ]);
    }

    public function getName()
    {
        return 'import_group_settings';
    }
}
