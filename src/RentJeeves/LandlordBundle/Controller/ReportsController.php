<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Form\BaseOrderReportType;
use RentJeeves\LandlordBundle\Form\ImportFileAccountingType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/reports")
 */
class ReportsController extends Controller
{
    const IMPORT_FILE_NAME = 'importFileName';

    const IMPORT_PROPERTY_ID = 'importPropertyId';

    protected function checkAccessToReport()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (!$user->haveAccessToReports()) {
            throw new Exception("Don't have access");
        }
    }

    protected function getImportData()
    {
        $session = $this->get('session');
        $data = array(
            self::IMPORT_FILE_NAME      => $session->get(self::IMPORT_FILE_NAME),
            self::IMPORT_PROPERTY_ID    => $session->get(self::IMPORT_PROPERTY_ID),
        );

        if (empty($data[self::IMPORT_FILE_NAME]) || empty($data[self::IMPORT_PROPERTY_ID])) {
            return false;
        }

        $data[self::IMPORT_FILE_NAME] = sys_get_temp_dir().DIRECTORY_SEPARATOR.$data[self::IMPORT_FILE_NAME];

        return $data;
    }

    /**
     * @Route(
     *     "/export",
     *     name="landlord_reports_export"
     * )
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function exportAction(Request $request)
    {
        $this->checkAccessToReport();
        if ($request->getMethod() == 'POST') {
            $form = $request->request->get('base_order_report_type');
            $validationRule = array(
                $form['type']
            );
        } else {
            $validationRule = array('xml');
        }

        $group = $this->get('core.session.landlord')->getGroup();
        $formBaseOrder = $this->createForm(
            new BaseOrderReportType($this->getUser(), $group, $validationRule)
        );

        $formBaseOrder->handleRequest($this->get('request'));
        if ($formBaseOrder->isValid()) {

            $data = $formBaseOrder->getData();
            $baseReport = $this->get('report.order');
            $report = $baseReport->getReport($data);

            $response = new Response();
            $response->setContent($report);
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-Type', $baseReport->getContentType());
            $response->headers->set('Content-Disposition', 'attachment; filename='.$baseReport->getFileName());

            return $response;
        }

        return array(
            'settings'           => $this->getUser()->getSettings(),
            'formBaseOrder'      => $formBaseOrder->createView(),
            'nGroups'            => $this->getGroups()->count(),
        );
    }

    /**
     * @Route(
     *     "/file/import",
     *     name="landlord_reports_import"
     * )
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function importFileAction(Request $request)
    {
        $this->checkAccessToReport();
        $form = $this->createForm(
            new ImportFileAccountingType($this->getUser())
        );

        $form->handleRequest($this->get('request'));
        if (!$form->isValid()) {
            return array(
                'form'      => $form->createView(),
            );
        }

        $file = $form['attachment']->getData();
        $property = $form['property']->getData();
        $tmpDir = sys_get_temp_dir();
        $newFileName = uniqid().'.csv';
        $file->move($tmpDir, $newFileName);
        $session = $request->getSession();
        $session->set(self::IMPORT_FILE_NAME, $newFileName);
        $session->set(self::IMPORT_PROPERTY_ID, $property->getId());

        return $this->redirect($this->generateUrl('landlord_reports_match_file'));
    }

    /**
     * @Route(
     *     "/match/file",
     *     name="landlord_reports_match_file"
     * )
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function matchFileAction(Request $request)
    {
        $this->checkAccessToReport();
        if (!$data = $this->getImportData()) {
            $this->redirect($this->generateUrl('landlord_reports_match_file'));
        }

        /**
         * @var $reader CsvFileReader
         */
        $reader = $this->get('reader.csv');
        $data = $reader->read($data[self::IMPORT_FILE_NAME]);

        if (count($data) < 3) {
            return array(
                'error' => 'csv.file.too.small1'
            );
        }

        if (count($data[1]) < 8) {
            return array(
                'error' => 'csv.file.too.small2'
            );
        }

        $dataView = array();
        $headers = array_keys($data[1]);

        for ($i=1; $i < count($data[1]); $i++) {
            $dataView[] = array(
                'name' => $headers[$i-1],
                'row1' => $data[1][$headers[$i-1]],
                'row2' => $data[2][$headers[$i-1]]
            );
        }


        return array(
            'error'        => false,
            'data'         => $dataView,
        );

    }
}
