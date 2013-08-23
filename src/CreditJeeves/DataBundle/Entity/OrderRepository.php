<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
        $query->where('t.group = :group');
        $query->setParameter('group', $group);
        if (!empty($search) && !empty($searchBy)) {
            $this->applySearchField($searchBy);
            $query->andWhere($searchBy.' LIKE :search');
            $query->setParameter('search', '%'.$search.'%');
        }
        $query = $query->getQuery();
        return $query->getScalarResult();
    }


    private function applySearchField(&$field)
    {
        switch ($field) {
            case 'status':
            case 'amount':
                $field = 'o.'.$field;
                break;
            case 'property':
                $field = 'prop.street';
                break;
            case 'tenant':
                $field = 'CONCAT(ten.first_name, ten.last_name)';
                break;
            default:
                $field = 'o.status';
                break;
        }
    }


    private function applyField(&$field)
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
     * 
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
        $query->where('t.group = :group');
        //$query->andWhere('c.paid_to > :date OR c.paid_to IS NULL  OR c.status = :status');
        $query->setParameter('group', $group);
        //         $query->setParameter('date', new \Datetime());
        //         $query->setParameter('status', ContractStatus::FINISHED);
        if (!empty($search) && !empty($searchBy)) {
            $this->applySearchField($searchBy);
            $query->andWhere($searchBy.' LIKE :search');
            $query->setParameter('search', '%'.$search.'%');
        }
        $this->applyField($sort);
        $query->orderBy($sort, $order);
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query = $query->getQuery();
        return $query->execute();
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
}
