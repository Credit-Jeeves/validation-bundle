<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Model\Unit;
use RentJeeves\LandlordBundle\Accounting\ImportMapping as Import;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use \DateTime;
use \SimpleXMLElement;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 */
class ImportCase extends BaseTestCase
{
    protected $mapFile = array(
        '1' => Import::KEY_UNIT,
        '4' => Import::KEY_RESIDENT_ID,
        '5' => Import::KEY_TENANT_NAME,
        '7' => Import::KEY_RENT,
        '10'=> Import::KEY_MOVE_IN,
        '11'=> Import::KEY_LEASE_END,
        '12'=> Import::KEY_MOVE_OUT,
        '13'=> Import::KEY_BALANCE,
        '14'=> Import::KEY_EMAIL,
    );

    protected function getFilePathByName($fileName)
    {
        $sep = DIRECTORY_SEPARATOR;
        $filePath = getcwd();
        $filePath .= $sep.'data'.$sep.'fixtures'.$sep.$fileName;
        return $filePath;
    }

    protected function waitReviewAndPost()
    {
        $this->session->wait(
            5000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            5000,
            "$('.overlay-trigger').length <= 0"
        );

        $this->session->wait(
            5000,
            "$('.submitImportFile>span').is(':visible')"
        );
    }

    protected function getParsedTrsByStatus()
    {
        $result = array();
        $this->assertNotNull($tr = $this->page->findAll('css', '.properties-table>tbody>tr'));
        $counterTr = count($tr);
        for ($k = 0; $k < $counterTr; $k++) {
            $td = $tr[$k]->findAll('css', 'td');
            $countedTd = count($td);
            if ($countedTd > 1 && !empty($td)) {
                $result[$td[0]->getHtml()][] = $tr[$k];
            }
        }

        return $result;
    }

    /**
     * @test
     */
    public function index()
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('tab.accounting');
        //First Step
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertEquals($error->getHtml(), 'This value should not be blank.');

        // attach file to file input:
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import_failed.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNotNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertEquals($error->getHtml(), 'csv.file.too.small2');
        $this->assertNotNull($prev = $this->page->find('css', '.button'));
        $prev->click();
        $this->assertNotNull($attFile = $this->page->find('css', '#import_file_type_attachment'));
        $filePath = $this->getFilePathByName('import.csv');
        $attFile->attachFile($filePath);
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->assertNull($error = $this->page->find('css', '.error_list>li'));
        $this->assertNotNull($table = $this->page->find('css', 'table'));

        for ($i = 1; $i <= 14; $i++) {
            $this->assertNotNull($choice = $this->page->find('css', '#import_match_file_type_column'.$i));
            if (isset($this->mapFile[$i])) {
                $choice->selectOption($this->mapFile[$i]);
            }
        }

        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile'));
        $submitImportFile->click();
        $this->session->wait(
            5000,
            "$('.errorField').length > 0"
        );
        $this->assertNotNull($errorField = $this->page->find('css', '.errorField'));
        $this->assertEquals($errorField->getHtml(), 't0*013202');

        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(count($trs), 5, "Count statuses is wrong");
        $this->assertEquals(count($trs['import.status.error']), 1, "Error contract on first page is wrong number");
        $this->assertEquals(count($trs['import.status.new']), 3, "New contract on first page is wrong number");
        $this->assertEquals(count($trs['import.status.skip']), 3, "Skip contract on first page is wrong number");
        $this->assertEquals(count($trs['import.status.match']), 1, "Match contract on first page is wrong number");
        $this->assertEquals(count($trs['import.status.ended']), 2, "Ended contract on first page is wrong number");
        $this->assertNotNull($submitImportFile = $this->page->find('css', '.submitImportFile>span'));
        $submitImportFile->click();
        $this->waitReviewAndPost();
        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(count($errorFields), 2);

        $this->assertNotNull(
            $email = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_tenant_email')
        );
        $email->setValue('2test@mail.com');
        $submitImportFile->click();

        $this->waitReviewAndPost();

        $submitImportFile->click();

        $this->waitReviewAndPost();

        $this->assertNotNull($errorFields = $this->page->findAll('css', '.errorField'));
        $this->assertEquals(count($errorFields), 4);
        $trs = $this->getParsedTrsByStatus();

        $this->assertEquals(count($trs), 2, "Count statuses is wrong");
        $this->assertEquals(count($trs['import.status.new']), 6, "New contract on first page is wrong number");
        $this->assertEquals(count($trs['import.status.skip']), 3, "Skip contract on first page is wrong number");

        $this->assertNotNull(
            $finishAt = $trs['import.status.new'][0]->find('css', '.import_new_user_with_contract_contract_finishAt')
        );
        $finishAt->setValue('03/31/2014');

        $this->assertNotNull(
            $firstName = $trs['import.status.new'][1]->find('css', '.import_new_user_with_contract_tenant_first_name')
        );
        $firstName->setValue('Jung');

        $this->assertNotNull(
            $lastName = $trs['import.status.new'][1]->find('css', '.import_new_user_with_contract_tenant_last_name')
        );
        $lastName->setValue('Sophia');

        $this->assertNotNull(
            $finishAt = $trs['import.status.new'][2]->find('css', '.import_new_user_with_contract_contract_finishAt')
        );
        $finishAt->setValue('03/31/2014');

        $this->assertNotNull(
            $lastName = $trs['import.status.new'][3]->find('css', '.import_new_user_with_contract_tenant_last_name')
        );
        $lastName->setValue('Jr');

        $submitImportFile->click();

        $this->session->wait(
            5000,
            "$('.finishedTitle').length > 0"
        );

        $this->assertNotNull($finishedTitle = $this->page->find('css', '.finishedTitle'));
        $this->assertEquals($finishedTitle->getHtml(), 'import.review.finish');

        //Check notify tenant invite for new user
        $this->setDefaultSession('goutte');
        $this->visitEmailsPage();
        $this->assertNotNull($email = $this->page->findAll('css', 'a'));
        $this->assertCount(9, $email, 'Wrong number of emails');
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => '2test@mail.com',
            )
        );
        $this->assertNotNull($tenant);
        $this->assertEquals($tenant->getFirstName(), 'Trent Direnna');
        $this->assertEquals($tenant->getLastName(), 'Jacquelyn Dacey');
        $this->assertEquals($tenant->getLastName(), 'Jacquelyn Dacey');
        $this->assertEquals($tenant->getResidentId(), 't0019851');
        /**
         * @var $contract Contract
         */
        $contract = $tenant->getContracts()->first();
        /**
         * @var $Unit Unit
         */
        $unit = $contract->getUnit();
        $this->assertEquals($unit->getName(), '1017B');
        $this->assertEquals($contract->getStatus(), ContractStatus::INVITE);
        $this->assertEquals($contract->getRent(), '1200');
        $this->assertEquals($contract->getImportedBalance(), '0');
        $this->assertEquals($contract->getStartAt()->format('m/d/Y'), '11/09/2013');
        $this->assertEquals($contract->getFinishAt()->format('m/d/Y'), '11/08/2014');

        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'tenant11@example.com',
            )
        );

        $contracts = $tenant->getContracts();
        $contractEnded = null;
        $contractMatch = null;
        foreach ($contracts as $contract) {
            if ($contract->getUnit()->getName() === '1-b') {
                $contractMatch = $contract;
                continue;
            }

            if ($contract->getUnit()->getName() === '2-a') {
                $contractEnded = $contract;
                continue;
            }
        }

        $this->assertEquals($contractEnded->getStatus(), ContractStatus::FINISHED);
        $this->assertEquals($contractEnded->getFinishAt()->format('m/d/Y'), '03/01/2011');

        $this->assertEquals($contractMatch->getRent(), '1190');
        $this->assertEquals($contractMatch->getImportedBalance(), '0');
        $this->assertEquals($contractMatch->getStartAt()->format('m/d/Y'), '04/22/2010');
        $this->assertEquals($contractMatch->getFinishAt()->format('m/d/Y'), '10/21/2016');
        $this->assertEquals($contractMatch->getStatus(), ContractStatus::APPROVED);
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email' => 'hugo@rentrack.com',
            )
        );

        $contracts = $tenant->getContracts();
        $contractNew = null;
        foreach ($contracts as $contract) {
            if ($contract->getUnit()->getName() === '1017') {
                $contractNew = $contract;
                break;
            }
        }

        $this->assertEquals($contractNew->getRent(), '950');
        $this->assertEquals($contractNew->getImportedBalance(), '0');
        $this->assertEquals($contractNew->getStartAt()->format('m/d/Y'), '03/18/2011');
        $this->assertEquals($contractNew->getFinishAt()->format('m/d/Y'), '03/31/2015');
        $this->assertEquals($contractNew->getStatus(), ContractStatus::APPROVED);
    }
}
