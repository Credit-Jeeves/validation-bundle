<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

/**
 * form.upload_csv_file
 */
class UploadCsvFileType extends Base
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'attachment',
            'file',
            [
                'required'       => true,
                'label'          => 'csv.file',
                'mapped'         => false,
                'constraints'    => [
                    new NotBlank(
                        [
                            'message' => 'error.file.empty'
                        ]
                    ),
                    new File(
                        [
                            'maxSize' => '2M',
                            'mimeTypes' => [
                                'text/csv',
                                'text/plain'
                            ]
                        ]
                    )
                ],
            ]
        );
        $builder->add('upload', 'submit');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'upload_csv_file';
    }
}
