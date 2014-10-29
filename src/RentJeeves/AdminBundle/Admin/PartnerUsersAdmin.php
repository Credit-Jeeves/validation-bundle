<?php

namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\GroupType;
use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Entity\Partner;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Model\PartnerService;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Validator\Constraints\NotBlank;

class PartnerUsersAdmin extends Admin
{

    const TYPE = UserType::PARTNER;

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_'.self::TYPE.'_users';
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/'.self::TYPE.'_users';
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('email')
            ->add('full_name')
            ->add('partnerService', null, array('label' => 'Partner'))
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'partners' => array(
                            'template' => 'AdminBundle:CRUD:list__action_partners.html.twig'
                        ),
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add(
                'partnerService',
                'entity',
                array(
                    'label' => 'Partner',
                    'class' => 'RjDataBundle:PartnerService',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ps')
                            ->from('RjDataBundle:PartnerService', 'p');
                    }
                )
            )
            ->add('first_name')
            ->add('last_name')
            ->add('email');

        $parameterId = $this->request->get($this->getIdParameter());

        if (!empty($parameterId)) {
             $passwordOptions['required'] = false;
        } else {
             $passwordOptions['required'] = true;
             $passwordOptions['constraints'] = array(
                 new NotBlank(
                     array(
                         'groups'    => 'password',
                         'message'   => 'Password is required.'
                     )
                 )
             );
        }
        $formMapper->add(
            'password',
            'repeated',
            $passwordOptions + array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'validation_groups' => array('password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch'
            )
        );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email')
            ->add('partnerService.partner', null, array('label' => 'Partner'));
    }

    public function getClassnameLabel()
    {
        return 'partner users';
    }
}
