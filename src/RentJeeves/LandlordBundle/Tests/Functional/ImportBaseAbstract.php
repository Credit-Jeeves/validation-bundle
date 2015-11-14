<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;

class ImportBaseAbstract extends BaseTestCase
{
    protected $mapFile = [
        '1' => ImportMapping::KEY_UNIT,
        '4' => ImportMapping::KEY_RESIDENT_ID,
        '5' => ImportMapping::KEY_TENANT_NAME,
        '7' => ImportMapping::KEY_RENT,
        '10' => ImportMapping::KEY_MOVE_IN,
        '11' => ImportMapping::KEY_LEASE_END,
        '12' => ImportMapping::KEY_MOVE_OUT,
        '13' => ImportMapping::KEY_BALANCE,
        '14' => ImportMapping::KEY_EMAIL,
    ];

    protected $mapMultiplePropertyFile = [
        '1' => ImportMapping::KEY_RESIDENT_ID,
        '2' => ImportMapping::KEY_TENANT_NAME,
        '3' => ImportMapping::KEY_RENT,
        '4' => ImportMapping::KEY_BALANCE,
        '5' => ImportMapping::KEY_UNIT_ID,
        '6' => ImportMapping::KEY_STREET,
        '8' => ImportMapping::KEY_UNIT,
        '9' => ImportMapping::KEY_CITY,
        '10' => ImportMapping::KEY_STATE,
        '11' => ImportMapping::KEY_ZIP,
        '13' => ImportMapping::KEY_MOVE_IN,
        '14' => ImportMapping::KEY_LEASE_END,
        '15' => ImportMapping::KEY_MOVE_OUT,
        '16' => ImportMapping::KEY_MONTH_TO_MONTH,
        '17' => ImportMapping::KEY_EMAIL,
    ];

    protected $mapMultipleGroupFile = [
        '1' => ImportMapping::KEY_GROUP_ACCOUNT_NUMBER,
        '2' => ImportMapping::KEY_RESIDENT_ID,
        '3' => ImportMapping::KEY_TENANT_NAME,
        '4' => ImportMapping::KEY_RENT,
        '5' => ImportMapping::KEY_BALANCE,
        '6' => ImportMapping::KEY_UNIT_ID,
        '7' => ImportMapping::KEY_STREET,
        '9' => ImportMapping::KEY_UNIT,
        '10' => ImportMapping::KEY_CITY,
        '11' => ImportMapping::KEY_STATE,
        '12' => ImportMapping::KEY_ZIP,
        '14' => ImportMapping::KEY_MOVE_IN,
        '15' => ImportMapping::KEY_LEASE_END,
        '16' => ImportMapping::KEY_MOVE_OUT,
        '18' => ImportMapping::KEY_EMAIL,
    ];

    /**
     * @return ContractWaiting
     */
    protected function getWaitingRoom()
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $contractWaiting = $em->getRepository('RjDataBundle:ContractWaiting')->findBy(
            array(
                'residentId' => 't0019851',
            )
        );

        $this->assertNotNull($contractWaiting);
        $this->assertEquals(1, count($contractWaiting));
        /**
         * @var $contractWaiting ContractWaiting
         */
        $contractWaiting = reset($contractWaiting);
        $this->assertNotNull($contractWaiting);

        return $contractWaiting;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getFilePathByName($fileName)
    {
        $sep = DIRECTORY_SEPARATOR;
        $filePath = getcwd();
        $filePath .= $sep . 'data' . $sep . 'fixtures' . $sep . $fileName;

        return $filePath;
    }

    protected function setPropertyFirst()
    {
        $em = $this->getEntityManager();
        $property = $em->getRepository('RjDataBundle:Property')->findOneByPropertyAddressFields(
            [
                'street' => 'Broadway',
                'number' => '770',
                'zip' => '10003'
            ]
        );
        $this->assertNotNull($propertySelector = $this->page->find('css', '#import_file_type_property'));
        $propertySelector->selectOption($property->getId());
    }

    /**
     * @return array
     */
    protected function getParsedTrsByStatus()
    {
        $result = [];
        $tds = $this->page->findAll(
            'css',
            '#importTable>tbody>tr>td.import_status_text'
        );

        foreach ($tds as $td) {
            $result[$td->getHtml()][] = $td->getParent();
        }

        return $result;
    }

    protected function waitReviewAndPost()
    {
        $this->session->wait(
            10000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            21000,
            "$('.overlay-trigger').length <= 0"
        );

        $this->session->wait(
            10000,
            "$('.submitImportFile>span').is(':visible')"
        );
    }

    protected function waitRedirectToSummaryPage()
    {
        $this->session->wait(
            1500000,
            "$('#summaryList').length > 0"
        );
    }
}
