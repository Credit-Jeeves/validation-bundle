<?php

// Holding: The Baumgartner Company (18947)
// Group: Melbourne Commons (71277)
// Property ID: 84a9d23e-7a46-4dde-818f-79170b60263b

/* Sample Unit:

                    <RT_Unit>
                        <UnitID>3207-101</UnitID>
                        <NumberOccupants Total="0" />
                        <Unit>
                            <MITS:Information>
                                <MITS:UnitID>3207-101</MITS:UnitID>         <!-- Unit name after dash -->
                                <MITS:UnitType>3X3</MITS:UnitType>
                                <MITS:UnitBedrooms>3</MITS:UnitBedrooms>
                                <MITS:UnitBathrooms>3.00</MITS:UnitBathrooms>
                                <MITS:MinSquareFeet>1430</MITS:MinSquareFeet>
                                <MITS:MaxSquareFeet>1430</MITS:MaxSquareFeet>
                                <MITS:MarketRent>2100.00</MITS:MarketRent>
                                <MITS:UnitEconomicStatus>residential</MITS:UnitEconomicStatus>
                                <MITS:UnitOccupancyStatus>vacant</MITS:UnitOccupancyStatus>
                                <MITS:UnitLeasedStatus>available</MITS:UnitLeasedStatus>
                                <MITS:FloorPlanID>3X3</MITS:FloorPlanID>
                                <MITS:FloorplanName>3 Bedroom, 3 Bathroom</MITS:FloorplanName>
                                <MITS:BuildingID>3207</MITS:BuildingID>     <!-- Street number in Building -->
                                <MITS:Address Type="property">
                                    <MITS:Address1>3207 S. Babcock St</MITS:Address1>  <!-- Street name here -->
                                    <MITS:City>Melbourne</MITS:City>
                                    <MITS:State>FL</MITS:State>
                                    <MITS:PostalCode>32901</MITS:PostalCode>

                                </MITS:Address>

                            </MITS:Information>
                            <MITS:PropertyPrimaryID>84a9d23e-7a46-4dde-818f-79170b60263b</MITS:PropertyPrimaryID>
                            <MITS:MarketingName>Melbourne Commons </MITS:MarketingName>

                        </Unit>

                    </RT_Unit>

 */

namespace RentJeeves\ImportBundle\PropertyImport\Transformer\Custom;

use RentJeeves\ExternalApiBundle\Model\ResMan\RtUnit;
use RentJeeves\ImportBundle\PropertyImport\Transformer\ResmanTransformer;

class BaumgartnerMelbourneTransformer extends ResmanTransformer
{
    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getUnitName(RtUnit $rtUnit)
    {
        $bldUnit = explode("-", $rtUnit->getUnitId());
        $bldStreetNum = $bldUnit[0]; // we don't use here
        $bldUnitName = $bldUnit[1];
        return $bldUnitName;
    }

    /**
     * @param RtUnit $rtUnit
     *
     * @return string
     */
    protected function getAddress1(RtUnit $rtUnit)
    {
        // Remove the street number from the address and replace it with building ID (correct street number)
        $streetName = substr($rtUnit->getUnit()->getInformation()->getAddress()->getAddress1(), 4);
        return $this->getExternalBuildingId($rtUnit) . $streetName;
    }
}
