<?php
namespace RentJeeves\CheckoutBundle\Hydrators;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

class PaymentHydrator extends AbstractHydrator
{
    protected function hydrateAllData()
    {
        $result = array();
        $cache  = array();
        foreach($this->_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->hydrateRowData($row, $cache, $result);
        }
        return $result;
    }

    protected function hydrateRowData(array $row, array &$cache, array &$result)
    {
    }
}