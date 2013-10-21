<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\UserType;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\QueryBuilder;


use Knp\Menu\ItemInterface as MenuItemInterface;

class LandlordAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = UserType::LANDLORD;

    protected $formOptions = array(
            'validation_groups' => 'user_admin'
    );

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
        $group = $this->getModelManager()->find('DataBundle:Group', $nGroupId);
        $holding = $group->getHolding();
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->leftJoin($alias.'.agent_groups', $alias.'_g');
        if (!empty($nGroupId)) {
            $this->request->getSession()->set('group_id', $nGroupId);
            $query->andWhere(
                '('.$alias.'_g.id = :group_id) OR ('.$alias.'.holding = :holding AND '.$alias.'.is_super_admin = true)'
            );
            $query->setParameter('group_id', $nGroupId);
            $query->setParameter('holding', $holding);
        }
        return $query;
    }
    
    public function configureListFields(ListMapper $listMapper)
    {
        $request = $this->getRequest();
        $request->getSession()->set('group_id', null);
        $listMapper
            ->add('full_name')
            ->add('holding')
            ->add('email')
            ->add('is_active')
            ->add('is_super_admin')
            ->add('last_login', 'date')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'observe' => array(
                            'template' => 'AdminBundle:CRUD:list__landlord_observe.html.twig'
                        ),
                        'groups' => array(
                            'template' => 'AdminBundle:CRUD:list__landlord_groups.html.twig'
                        ),
                        'properties' => array(
                                'template' => 'AdminBundle:CRUD:list__landlord_properties.html.twig'
                        )
                    )
                )
            );
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $entity = $this->getSubject();
        $query = $this->getModelManager()->createQuery('DataBundle:Group', 'g');
        $query->innerJoin('g.holding', 'h');
        $query->where('h.id = :holding_id');
        $query->orderBy('g.name');
        $query->setParameter('holding_id', $entity->getHoldingId());
        $formMapper
            ->with('General')
                ->add(
                    'holding',
                    'sonata_type_model'
                )
                ->add('first_name')
                ->add(
                    'middle_initial',
                    null,
                    array(
                        'required' => false
                    )
                )
                ->add('last_name')
                ->add('email')
                ->add('password', 'hidden', array('required' => false))
                ->add('password_new', 'password', array('required' => false, 'mapped' => false))
                ->add('password_retype', 'password', array('required' => false, 'mapped' => false))
                ->add('is_active', null, array('required' => false))
                ->add('is_super_admin', null, array('required' => false))
            ->end()
            ->with('Permissions')
                ->add(
                    'agent_groups',
                    'sonata_type_model',
                    array(
                       'required' => false,
                       'expanded' => true,
                       'multiple' => true,
                       'query' => $query,
                    )
                )
            ->end();
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('is_active');
    }

    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
        $menu = $this->menuFactory->createItem('root');
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'dashboard',
                    'breadcrumb',
                    'link'
                ),
                array(),
                'SonataAdminBundle'
            ),
            array(
                'uri' => $this->routeGenerator->generate('sonata_admin_dashboard')
            )
        );
        if ('list' == $action & !empty($nGroupId)) {
            $menu = $menu->addChild(
                $this->trans(
                    $this->getLabelTranslatorStrategy()->getLabel(
                        'Group List',
                        'breadcrumb',
                        'link'
                    ),
                    array(),
                    'SonataAdminBundle'
                ),
                array(
                    'uri' => $this->routeGenerator->generate('admin_rj_group_list')
                )
            );
        }
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'Landlord List',
                    'breadcrumb',
                    'link'
                ),
                array(),
                'SonataAdminBundle'
            ),
            array(
                'uri' => $this->routeGenerator->generate('admin_creditjeeves_data_order_list')
            )
        );
        return $this->breadcrumbs[$action] = $menu;
    }
    

    /**
     * {@inheritdoc}
     */
    public function preUpdate($user)
    {
        $user = $this->checkPassword($user);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($user)
    {
        $user->setType(self::TYPE);
        $user = $this->checkPassword($user);
    }

    private function checkPassword($user)
    {
        $isValid = false;
        $password = $user->getPassword();
        $request = $this->getRequest();
        $password_new = $formData['password_new'];
        $password_retype = $formData['password_retype'];
        if (!empty($password)) {
            $isValid = true;
        }
        if (!empty($password_new) && $password_new === $password_retype) {
            $isValid = true;
            // FIXME DO NOT HARDCODE IT!!!
            $user->setPassword(md5($password_new));
        }
        if (!$isValid) {
            $request->getSession()->getFlashBag()->add(
                'sonata_flash_error',
                'Please, enter password for '.$user->getFullName()
            );
        }
        return $user;
    }
}
