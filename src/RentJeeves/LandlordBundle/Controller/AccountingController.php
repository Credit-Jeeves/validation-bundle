<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerBuilder;
use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Form\BaseOrderReportType;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportFileAccountingType;
use RentJeeves\LandlordBundle\Form\ImportMatchFileType;
use RentJeeves\LandlordBundle\Form\ImportNewContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
use RentJeeves\LandlordBundle\Form\ImportUpdateContractType;
use RentJeeves\LandlordBundle\Report\AccountingImport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

/**
 * @Route("/accounting")
 */
class AccountingController extends Controller
{
    protected function checkAccessToReport()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if (!$user->haveAccessToReports()) {
            throw new Exception("Don't have access");
        }
    }

    /**
     * @Route(
     *     "/export",
     *     name="accounting_export"
     * )
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
            $baseReport = $this->get('report.order.export');
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
     *     "/import/file",
     *     name="accounting_import_file"
     * )
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
        $textDelimiter = $form['textDelimiter']->getData();
        $fieldDelimiter = $form['fieldDelimiter']->getData();
        $tmpDir = sys_get_temp_dir();
        $newFileName = uniqid().'.csv';
        $file->move($tmpDir, $newFileName);
        /**
         * @var AccountingImport $accountingImport
         */
        $accountingImport = $this->get('accounting.import');

        $accountingImport->setFieldDelimiter($fieldDelimiter);
        $accountingImport->setTextDelimiter($textDelimiter);
        $accountingImport->setFilePath($newFileName);
        $accountingImport->setPropertyId($property->getId());

        return $this->redirect($this->generateUrl('accounting_match_file'));
    }

    /**
     * @Route(
     *     "/import/match/file",
     *     name="accounting_match_file"
     * )
     * @Template()
     */
    public function matchFileAction(Request $request)
    {
        $this->checkAccessToReport();
        /**
         * @var AccountingImport $accountingImport
         */
        $accountingImport = $this->get('accounting.import');
        if (!$data = $accountingImport->getImportData()) {
            $this->redirect($this->generateUrl('accounting_import_file'));
        }

        $data = $accountingImport->getDataForMapping();
        if (is_string($data)) {
            return array(
                'error' => $data
            );
        }

        $dataView = array();
        $headers = array_keys($data[1]);

        for ($i=1; $i < count($data[1])+1; $i++) {
            $dataView[] = array(
                'name' => $headers[$i-1],
                'row1' => $data[1][$headers[$i-1]],
                'row2' => (isset($data[2]))? $data[2][$headers[$i-1]] : null,
                'form' => ImportMatchFileType::getFieldNameByNumber($i),
            );
        }

        $form = $this->createForm(
            new ImportMatchFileType(count($dataView))
        );
        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $result = array();
            for ($i=1; $i < count($data[1])+1; $i++) {
                $nameField = ImportMatchFileType::getFieldNameByNumber($i);
                $value = $form->get($nameField)->getData();
                if ($value === ImportMatchFileType::EMPTY_VALUE) {
                    continue;
                }

                $result[$i] = $value;
            }

            $accountingImport->setMapping($result);
            $accountingImport->setFileLine(0);

            return $this->redirect($this->generateUrl('accounting_import'));
        }

        $form = $form->createView();

        return array(
            'error'        => false,
            'data'         => $dataView,
            'form'         => $form,
        );
    }
    /**
     * @Route(
     *     "/import",
     *     name="accounting_import"
     * )
     * @Template()
     */
    public function importAction(Request $request)
    {
        $this->checkAccessToReport();
        /**
         * @var AccountingImport $accountingImport
         */
        $accountingImport = $this->get('accounting.import');
        if (!$data = $accountingImport->getImportData()) {
            $this->redirect($this->generateUrl('accounting_import_file'));
        }

        if (empty($data[$accountingImport::IMPORT_MAPPING])) {
            $this->redirect($this->generateUrl('accounting_import_file'));
        }


        $formNewUserWithContract = $accountingImport->getCreateUserAndCreateContractForm();
        $formContract = $accountingImport->getContractForm();
        $formContractFinish = $accountingImport->getContractFinishForm();

        return array(
            'formNewUserWithContract' => $formNewUserWithContract->createView(),
            'formContract'            => $formContract->createView(),
            'formContractFinish'      => $formContractFinish->createView(),
        );
    }

    /**
     * @Route(
     *     "/import/getRows",
     *     name="accounting_import_get_rows",
     *     options={"expose"=true}
     * )
     */
    public function getRowsAction(Request $request)
    {
        $this->checkAccessToReport();
        $result = array(
            'error'   => false,
            'message' => '',
        );
        $i18n = $this->get('translator');
        /**
         * @var AccountingImport $accountingImport
         */
        $accountingImport = $this->get('accounting.import');
        if (!$data = $accountingImport->getImportData()) {
            $result['error'] = true;
            $result['message'] = $i18n->trans('import.error.access');
            return new JsonResponse($result);
        }

        if (empty($data[$accountingImport::IMPORT_MAPPING])) {
            $result['error'] = true;
            $result['message'] =  $i18n->trans('import.error.access');
            return new JsonResponse($result);
        }

        $newRows = filter_var($request->request->get('newRows', false), FILTER_VALIDATE_BOOLEAN);

        if ($newRows) {
            $accountingImport->setFileLine($accountingImport->getFileLine() + $accountingImport::ROW_ON_PAGE);
        }

        $rows = array();
        $total = $accountingImport->countData();

        if ($total > 0) {
            $rows = $accountingImport->getMappedData();
        }

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('RentJeevesImport');

        $result['rows'] = $rows;
        $result['total'] = $total;

        return new Response($this->get('jms_serializer')->serialize($result, 'json', $context));
    }

    /**
     * @Route(
     *     "/import/save/rows",
     *     name="accounting_import_save_rows",
     *     options={"expose"=true}
     * )
     */
    public function saveRowsAction(Request $request)
    {
        $this->checkAccessToReport();
        $result = array(
            'error'   => false,
            'message' => '',
        );
        $i18n = $this->get('translator');
        /**
         * @var AccountingImport $accountingImport
         */
        $accountingImport = $this->get('accounting.import');
        if (!$data = $accountingImport->getImportData()) {
            $result['error'] = true;
            $result['message'] = $i18n->trans('import.error.access');
            return new JsonResponse($result);
        }

        if (empty($data[$accountingImport::IMPORT_MAPPING])) {
            $result['error'] = true;
            $result['message'] =  $i18n->trans('import.error.access');
            return new JsonResponse($result);
        }

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('RentJeevesImport');

        $data = $request->request->all();
        $errors               = $accountingImport->saveForms($data);
        $result['formErrors'] = $errors;

        return new Response($this->get('jms_serializer')->serialize($result, 'json', $context));
    }
}
