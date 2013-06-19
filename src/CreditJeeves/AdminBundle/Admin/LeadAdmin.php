<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Knp\Menu\FactoryInterface as MenuFactoryInterface;

class LeadAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'lead';

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_cj_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/cj/'.self::TYPE;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('export');
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        if (!empty($nUserId)) {
            $this->request->getSession()->set('user_id', $nUserId);
            $query->andWhere($alias.'.cj_account_id = :user_id');
            $query->setParameter('user_id', $nUserId);
        }
        if (!empty($nGroupId)) {
            $this->request->getSession()->set('group_id', $nGroupId);
            $query->andWhere($alias.'.cj_group_id = :group_id');
            $query->setParameter('group_id', $nGroupId);
        }
        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add(
                'user.full_name',
                'text',
                array(
                    'label' => 'Applicant'
                )
            )
            ->add(
                'dealer.full_name',
                'text',
                array(
                    'label' => 'Dealer'
                )
            )
            ->add(
                'current_score',
                'text',
                array(
                    'label' => 'Score'
                )
            )
            ->add('target_score')
            ->add('status')
            ->add(
                'user.is_active',
                'boolean',
                array(
                    'label' => 'Is active'
                )
            )
            ->add('updatedAt', 'date')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'view' => array(),
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('target_score');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('status')
            ->add('target_score');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $filter)
    {
        $filter
        ->add('target_score')
        ->add('target_name', 'text', array('label' => 'Goal'))
        ->add('status');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
//         if ('list' == $action) {
//             $item = $this->menuFactory->createItem('back', array(
//                 'uri' => 'javascript:void(send_test())',
//                 'label' => 'Send test email',
//             ));
//             $menu->addChild($item);
//         }
    }

    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
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
        if ('list' == $action & !empty($nUserId)) {
            $menu = $menu->addChild(
                $this->trans(
                    $this->getLabelTranslatorStrategy()->getLabel(
                        'Dealer List',
                        'breadcrumb',
                        'link'
                    ),
                    array(),
                    'SonataAdminBundle'
                ),
                array(
                    'uri' => $this->routeGenerator->generate('admin_cj_dealer_list')
                )
            );
        }
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
                    'uri' => $this->routeGenerator->generate('admin_cj_group_list')
                )
            );
        }
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'Lead List',
                    'breadcrumb',
                    'link'
                ),
                array(),
                'SonataAdminBundle'
            ),
            array(
                'uri' => $this->routeGenerator->generate('admin_cj_lead_list')
            )
        );
        return $this->breadcrumbs[$action] = $menu;
    }
}
