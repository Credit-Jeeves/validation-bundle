<?php

namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\HttpFoundation\Request;

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
            ->add('type')
            ->add('partner', null, array('label' => 'Partner'))
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array()
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add(
                'partner',
                'entity',
                array(
                    'label' => 'Partner',
                    'class' => 'RjDataBundle:Partner',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('ps')
                            ->from('RjDataBundle:Partner', 'p');
                    }
                )
            )
            ->add('first_name')
            ->add('last_name')
            ->add('email');

        $parameterId = $this->request->get($this->getIdParameter());
        if (empty($parameterId)) {
            $formMapper->add(
                'password',
                'repeated',
                array(
                    'type' => 'password',
                    'required' => true,
                    'options' => array('translation_domain' => 'FOSUserBundle'),
                    'first_options' => array('label' => 'form.password'),
                    'second_options' => array('label' => 'form.password_confirmation'),
                    'invalid_message' => 'fos_user.password.mismatch'
                )
            );
        }
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email')
            ->add('partner.partner', null, array('label' => 'Partner'));
    }

    public function getClassnameLabel()
    {
        return 'partner users';
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($user)
    {
        $request = $this->getRequest();
        $formData = $request->request->get($this->getUniqid());
        $password = $formData['password']['first'];
        $user->setPassword(md5($password));
    }
}
