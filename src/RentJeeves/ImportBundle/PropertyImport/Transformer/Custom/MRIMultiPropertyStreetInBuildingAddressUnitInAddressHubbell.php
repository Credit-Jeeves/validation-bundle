<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ImportBundle\PropertyImport\Transformer\MRITransformer;

/* Sample entry:
  <entry>
    <ResidentNameID>DB00011845</ResidentNameID>
    <PropertyID>870-0000</PropertyID>
    <BuildingID>1</BuildingID>
    <UnitID>101</UnitID>
    <LeaseID>2</LeaseID>
    <Address>#101</Address>
    <BuildingAddress>6370 EP True Parkway</BuildingAddress>
    <City>West Des Moines</City>
    <State>IA</State>
    <Zipcode>50266</Zipcode>
    <FirstName>Soandso</FirstName>
    <LastName>Whatshisname</LastName>
    <ResidentStatus>Current</ResidentStatus>
    <Email>soandsosmith@example.com</Email>
    <Birthday>1963-06-11T00:00:00.0000000</Birthday>
    <LeaseStart />
    <OccupyDate>2015-10-15T00:00:00.0000000</OccupyDate>
    <LeaseEnd>2016-10-14T00:00:00.0000000</LeaseEnd>
    <LeaseMonthlyRentAmount>1000.00</LeaseMonthlyRentAmount>
    <LeaseMoveOut />
    <LeaseMonthToMonth>N</LeaseMonthToMonth>
    <PayAllowed />
    <LastUpdateDate>2015-10-15T00:00:00.0000000</LastUpdateDate>
    <CurrCode />
    <LeaseBalance>0.00</LeaseBalance>
    <IsCurrent>Y</IsCurrent>
    <BlockEPayments />
  </entry>
 */

class MRIMultiPropertyStreetInBuildingAddressUnitInAddressHubbell extends MRITransformer
{
    /**
     * {@inheritdoc}
     */
    protected function getAllowMultipleProperties(Value $accountingSystemRecord)
    {
        return true;
    }


    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getAddress1(Value $accountingSystemRecord)
    {
        return $accountingSystemRecord->getBuildingAddress();
    }

    /**
     * @param Value $accountingSystemRecord
     *
     * @return string
     */
    protected function getUnitName(Value $accountingSystemRecord)
    {
        $unit_name = $accountingSystemRecord->getUnitId();

        return ($accountingSystemRecord->getBuildingId() == "NRG") ? "NRG" . "$unit_name" : $unit_name;
    }
}
