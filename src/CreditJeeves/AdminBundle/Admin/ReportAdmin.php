<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ReportAdmin extends Admin
{
    protected $datagridValues = array(
        '_page'       => 1,
        '_sort_order' => 'DESC', // sort direction
        '_sort_by'    => 'created_at' // field name
    );

    /**
     *
     * @var string
     */
    const TYPE = 'report';

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

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        if (!empty($nUserId)) {
            $this->request->getSession()->set('user_id', $nUserId);
            $query->andWhere($alias.'.cj_applicant_id = :user_id');
            $query->setParameter('user_id', $nUserId);
        }
        return $query;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('export');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
             ->add('report_score')
             ->add('total_monthly_payment', 'money')
             ->add('created_at', 'date')
             ->add(
                 '_action',
                 'actions',
                 array(
                     'actions' => array(
                         'show' => array(),
                     )
                 )
             );
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('rawData');
    }

    public function getTemplate($name)
    {
        switch ($name) {
            case 'show':
                return 'AdminBundle:CRUD:arf_show.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }
}
