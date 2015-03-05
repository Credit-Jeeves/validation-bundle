<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ExportType extends AbstractType
{
    protected $user;

    protected $group;

    protected $validationGroups;

    protected $aviableValidationGroups = array(
        'yardi', 'promas', 'renttrack', 'yardi_genesis', 'real_page', 'yardi_genesis_v2'
    );

    public function __construct($user, $group = null, $validationGroups = array('yardi'))
    {
        $this->user  = $user;
        $this->group = $group;
        $this->setValidationGroup($validationGroups);

    }

    protected function setValidationGroup($validationGroups)
    {
        if (empty($validationGroups)) {
            throw new RuntimeException("Empty validation group");
        }

        foreach ($validationGroups as $group) {
            if (!in_array($group, $this->aviableValidationGroups)) {
                throw new RuntimeException(sprintf('Wrong validation group:%s', $group));
            }
        }

        $this->validationGroups = $validationGroups;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($isAdmin = $this->user->getIsSuperAdmin()) {
            $holding = $this->user->getHolding();
            $groups  = $holding->getGroups() ? $holding->getGroups() : null;
        } else {
            $groups = $this->user->getAgentGroups() ? $this->user->getAgentGroups() : null;
        }

        $builder->add(
            'begin',
            'date',
            array(
                'required'    => true,
                'input'       => 'string',
                'widget'      => 'single_text',
                'format'      => 'MM/dd/yyyy',
                'attr'        => array(
                    'class'     => 'begin calendar',
                    'data-bind' => 'datepicker: begin'
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array(
                                'yardi',
                                'promas',
                                'renttrack',
                                'yardi_genesis',
                                'yardi_genesis_v2',
                                'real_page'
                            )
                        )
                    ),
                    new Date(
                        array(
                            'groups' => array(
                                'yardi',
                                'promas',
                                'renttrack',
                                'yardi_genesis',
                                'yardi_genesis_v2',
                                'real_page'
                            )
                        )
                    ),
                )
            )
        );

        $builder->add(
            'end',
            'date',
            array(
                'input'       => 'string',
                'required'    => true,
                'widget'      => 'single_text',
                'format'      => 'MM/dd/yyyy',
                'attr'        => array(
                    'class' => 'end calendar',
                    'data-bind' => 'datepicker: end'
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array(
                                'yardi',
                                'promas',
                                'renttrack',
                                'yardi_genesis',
                                'yardi_genesis_v2',
                                'real_page'
                            )
                        )
                    ),
                    new Date(
                        array(
                            'groups' => array(
                                'yardi',
                                'promas',
                                'renttrack',
                                'yardi_genesis',
                                'yardi_genesis_v2',
                                'real_page'
                            )
                        )
                    ),
                )
            )
        );

        $builder->add(
            'export_by',
            'choice',
            [
                'attr'        => [
                    'force_row' => true,
                    'class'     => 'original',
                    'data-bind' => 'checked: exportBy'
                ],
                'required'    => true,
                'choices'     => [
                    'payments'  => 'Payments',
                    'deposits'   => 'Deposits'
                ],
                'data'       => 'deposits',
                'multiple'  => false,
                'expanded'  => true
            ]
        );

        $builder->add(
            'type',
            'choice',
            [
                'choices' => [
                    'yardi'             => 'order.report.type.yardi',
                    'real_page'         => 'order.report.type.realpage',
                    'promas'            => 'order.report.type.promas',
                    'renttrack'         => 'order.report.type.renttrack',
                    'yardi_genesis'     => 'order.report.type.yardi_genesis',
                    'yardi_genesis_v2'  => 'order.report.type.yardi_genesis_v2',
                ],
                'required'    => true,
                'attr'        => [
                    'force_row' => true,
                    'class' => 'original widthSelect',
                    'data-bind' => '
                        options: exportTypes,
                        optionsText: "typeName",
                        optionsValue: "type",
                        value: selectedType'
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => [
                                'yardi',
                                'promas',
                                'renttrack',
                                'yardi_genesis',
                                'yardi_genesis_v2',
                                'real_page'
                            ]
                        ]
                    )
                ],
            ]
        );

        $builder->add(
            'property',
            'entity',
            array(
                'class'          => 'RjDataBundle:Property',
                'error_bubbling' => true,
                'required'    => false,
                'attr'           => array(
                    'class' => 'original widthSelect',
                    'force_row' => true,
                    'data-bind' => '
                        options: properties,
                        optionsText: "name",
                        optionsValue: "id",
                        value: selectedProperty',
                    'row_attr' => array(
                        'data-bind' =>
                            "visible: (selectedType() != 'promas') &&
                             (selectedType() != 'renttrack') &&
                             (selectedType() != 'yardi_genesis_v2')
                             ",
                    )
                ),
                'constraints'    => array(
                    new NotBlank(
                        array(
                            'groups' => array('yardi', 'yardi_genesis', 'real_page')
                        )
                    ),
                ),
                'query_builder'  => function (EntityRepository $er) use ($groups) {

                    if ($this->group) {
                        $query = $er->createQueryBuilder('p');
                        $query->innerJoin('p.property_groups', 'g');
                        $query->where('g.id = :groupId');
                        $query->setParameter('groupId', $this->group->getId());
                        return $query;
                    }

                    if (!$groups) {
                        $query = $er->createQueryBuilder('p');
                        $query->where('p.id = 0');
                        return $query;
                    }

                    $ids = array();
                    foreach ($groups as $group) {
                        $ids[$group->getId()] = $group->getId();
                    }
                    $groupsIds = implode("','", $ids);
                    $query     = $er->createQueryBuilder('p');
                    $query->innerJoin('p.property_groups', 'g');
                    $query->where('g.id IN (:groupsIds)');
                    $query->setParameter('groupsIds', $groupsIds);

                    return $query;
                }
            )
        );

        $builder->add(
            'propertyId',
            'integer',
            array(
                'label'       => 'property.id',
                'required'    => false,
                'attr'        => array(
                    'class' => 'int',
                    'data-bind' => 'value: propertyId',
                    'row_attr' => array(
                        'data-bind' => "visible: selectedType() == 'yardi'",
                    )
                ),
                'constraints' => array(
                    new Regex(
                        array(
                            'pattern' => '/^[0-9]{1,10}$/',
                            'groups'  => array('yardi')
                        )
                    ),
                    new NotBlank(array('groups' => array('yardi')))
                )
            )
        );

        $builder->add(
            'buildingId',
            'integer',
            array(
                'label'       => 'building.id',
                'required'    => false,
                'attr'        => array(
                    'class' => 'int',
                    'data-bind' => 'value: buildingId',
                    'row_attr' => array(
                        'data-bind' => "visible: selectedType() == 'real_page'",
                    )
                ),
                'constraints' => array(
                    new Regex(
                        array(
                            'pattern' => '/^[0-9]{1,10}$/',
                            'groups'  => array('real_page')
                        )
                    ),
                    new NotBlank(array('groups' => array('real_page')))
                )
            )
        );

        $builder->add(
            'makeZip',
            'checkbox',
            [
                'label' => 'export.promas.make_zip',
                'required' => false,
                'attr' => [
                    'data-bind' => 'checked: makeZip'
                ],
                'label_attr' => [
                    'data-bind' => "visible: (exportBy() == 'deposits')",
                ]
            ]
        );

        $builder->add(
            'includeAllGroups',
            'checkbox',
            array(
                'label' => 'export.renttrack.include_all_groups',
                'required' => false,
                'attr' => array(
                    'data-bind' => 'checked: includeAllGroups',
                    'row_attr' => array(
                        'data-bind' => "visible: selectedType() == 'renttrack'",
                    )
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
                'validation_groups'  => $this->validationGroups,
            )
        );
    }

    public function getName()
    {
        return 'base_order_report_type';
    }
}
