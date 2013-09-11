<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use CreditJeeves\DataBundle\Enum\OrderStatus;

class OrderRepository extends EntityRepository
{
    /**
     *
     * @param \CreditJeeves\DataBundle\Entity\User $User
     */
    public function deleteUserOrders(\CreditJeeves\DataBundle\Entity\User $User)
    {
        $query = $this->createQueryBuilder('o')
                      ->delete()
                      ->where('o.cj_applicant_id = :id')
                      ->setParameter('id', $User->getId())
                      ->getQuery()
                      ->execute();
    }

    /**
     * 
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @param string $searchBy
     * @param string $search
     */
    public function countOrders(\CreditJeeves\DataBundle\Entity\Group $group, $searchBy = '', $search = '')
    {
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('t.unit', 'unit');
        $query->where('t.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search) && !empty($searchBy)) {
            $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy.' LIKE :search');
                $query->setParameter('search', '%'.$item.'%');
            }
        }
        $query = $query->getQuery();
        return $query->getScalarResult();
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     * @param integer $page
     * @param integer $limit
     * @param string $sort
     * @param string $order
     * @param string $searchBy
     * @param string $search
     */
    public function getOrdersPage(
        \CreditJeeves\DataBundle\Entity\Group $group,
        $page = 1,
        $limit = 100,
        $sort = 'o.status',
        $order = 'ASC',
        $searchBy = 'p.street',
        $search = ''
    ) {
        $offset = ($page - 1) * $limit;
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->innerJoin('p.contract', 't');
        $query->innerJoin('t.tenant', 'ten');
        $query->innerJoin('t.property', 'prop');
        $query->innerJoin('t.unit', 'unit');
        $query->where('t.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search) && !empty($searchBy)) {
            $this->applySearchField($searchBy);
            $search = $this->prepareSearch($search);
            foreach ($search as $item) {
                $query->andWhere($searchBy.' LIKE :search');
                $query->setParameter('search', '%'.$item.'%');
            }
        }
        switch ($sort) {
            case 'first_name':
                $query->orderBy('ten.first_name', $order);
                $query->addOrderBy('ten.last_name', $order);
                break;
            case 'date-posted':
                $query->orderBy('o.created_at', $order);
                break;
            case 'date-initiated':
                $query->orderBy('o.updated_at', $order);
                break;
            case 'property':
                $query->orderBy('prop.number', $order);
                $query->addOrderBy('prop.street', $order);
                $query->addOrderBy('unit.name', $order);
                break;
            default:
                $sort = 'o.'.$sort;
                $query->orderBy($sort, $order);
                break;
        }
        $this->applySortField($sort);
        
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
    }

    private function applySearchField(&$field)
    {
        switch ($field) {
            case 'status':
            case 'amount':
                $field = 'o.'.$field;
                break;
            case 'property':
                $field = 'CONCAT(prop.street, prop.number, unit.name)';
                break;
            case 'tenant':
                $field = 'CONCAT(ten.first_name, ten.last_name)';
                break;
            default:
                $field = 'o.status';
                break;
        }
    }

    private function applySortField(&$field)
    {
        switch ($field) {
            case 'status':
            case 'amount':
                $field = 'o.'.$field;
                break;
            case 'date-posted':
                $field = 'o.created_at';
                break;
            case 'date-initiated':
                $field = 'o.updated_at';
                break;
            case 'property':
                $field = 'prop.street';
                break;
            case 'tenant':
                $field = 'CONCAT(ten.first_name, ten.last_name)';
                break;
            case 'first_name':
                $field = 'ten.first_name';
                break;
            default:
                $field = 'o.status';
                break;
        }
    }

    /**
     * @param string $search
     * @return array
     */
    private function prepareSearch($search)
    {
        $search = preg_replace('/\s+/', ' ', trim($search));
        $search = explode(' ', $search);
        return $search;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     */
    public function getContractHistory(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->where('p.contract = :contract');
        $query->setParameter('contract', $contract);
        $query->orderBy('o.created_at', 'ASC');
        $query = $query->getQuery();
        return $query->execute();
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     */
    public function getLastContractPayment(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $query = $this->createQueryBuilder('o');
        $query->innerJoin('o.operations', 'p');
        $query->where('p.contract = :contract');
        $query->andWhere('o.status = :status');
        $query->setParameter('contract', $contract);
        $query->setParameter('status', OrderStatus::COMPLETE);
        $query->orderBy('o.created_at', 'DESC');
        $query->setMaxResults(1);
        $query = $query->getQuery();
        return $query->getOneOrNullResult();
    }
}
