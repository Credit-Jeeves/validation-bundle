<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderType;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerBuilder;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\LandlordBundle\Accounting\ImportMapping;
use RentJeeves\LandlordBundle\Accounting\ImportProcess;
use RentJeeves\LandlordBundle\Accounting\ImportStorage;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;
use RentJeeves\LandlordBundle\Form\ExportType;
use RentJeeves\LandlordBundle\Form\ImportContractType;
use RentJeeves\LandlordBundle\Form\ImportFileAccountingType;
use RentJeeves\LandlordBundle\Form\ImportMatchFileType;
use RentJeeves\LandlordBundle\Form\ImportNewContractType;
use RentJeeves\LandlordBundle\Form\ImportNewUserWithContractType;
use RentJeeves\LandlordBundle\Form\ImportUpdateContractType;
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
            new ExportType($this->getUser(), $group, $validationRule)
        );

        $formBaseOrder->handleRequest($this->get('request'));
        if ($formBaseOrder->isValid()) {

            $data = $formBaseOrder->getData();
            $baseReport = $this->get('accounting.export');
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
         * @var ImportStorage $importStorage
         */
        $importStorage = $this->get('accounting.import.storage');

        $importStorage->setFieldDelimiter($fieldDelimiter);
        $importStorage->setTextDelimiter($textDelimiter);
        $importStorage->setFilePath($newFileName);
        $importStorage->setPropertyId($property->getId());

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
        try {
            /**
             * @var ImportStorage $importStorage
             */
            $importStorage = $this->get('accounting.import.storage');
            $data = $importStorage->getImportData();
            /**
             * @var ImportMapping $importMapping
             */
            $importMapping = $this->get('accounting.import.mapping');
            $data = $importMapping->getDataForMapping();
        } catch (ImportStorageException $e) {
            $this->redirect($this->generateUrl('accounting_import_file'));
        } catch (ImportMappingException $e) {
            return array(
                'error' => $e->getMessage()
            );
        }

        $dataView = array();
        $headers = array_keys($data[1]);
        /**
         * Generate array values for view: 2 rows from csv file and choice  form field
         */
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

            $importStorage->setMapping($result);
            $importStorage->setFileLine(0);

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
         * @var ImportStorage $importStorage
         */
        $importStorage = $this->get('accounting.import.storage');
        try {
            $data = $importStorage->getImportData();
        } catch (ImportStorageException $e) {
            $this->redirect($this->generateUrl('accounting_import_file'));
        }

        if (empty($data[ImportStorage::IMPORT_MAPPING])) {
            $this->redirect($this->generateUrl('accounting_import_file'));
        }
        /**
         * @var $importProcess ImportProcess
         */
        $importProcess = $this->get('accounting.import.process');
        $formNewUserWithContract = $importProcess->getCreateUserAndCreateContractForm();
        $formContract = $importProcess->getContractForm();
        $formContractFinish = $importProcess->getContractFinishForm();

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
        $result = array(
            'error'   => false,
            'message' => '',
        );

        if (!$this->isAjaxRequestValid()) {
            $result['error'] = true;
            $result['message'] = $this->get('translator')->trans('import.error.access');

            return new JsonResponse($result);
        }

        /**
         * @var ImportStorage $importStorage
         */
        $importStorage = $this->get('accounting.import.storage');
        /**
         * @var ImportMapping $importMapping
         */
        $importMapping = $this->get('accounting.import.mapping');
        $newRows = filter_var($request->request->get('newRows', false), FILTER_VALIDATE_BOOLEAN);

        if ($newRows) {
            $importStorage->setFileLine($importStorage->getFileLine() + ImportProcess::ROW_ON_PAGE);
        }

        $rows = array();
        /**
         * @var $importProcess ImportProcess
         */
        $importProcess = $this->get('accounting.import.process');
        $total = $importMapping->countLines();

        if ($total > 0) {
            $rows = $importProcess->getMappedData();
        }

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('RentJeevesImport');

        $result['rows'] = $rows;
        $result['total'] = $total;

        $response = new Response($this->get('jms_serializer')->serialize($result, 'json', $context));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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
        if (!$this->isAjaxRequestValid()) {
            $result['error'] = true;
            $result['message'] = $this->get('translator')->trans('import.error.access');

            return new JsonResponse($result);
        }

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('RentJeevesImport');
        /**
         * @var $importProcess ImportProcess
         */
        $importProcess = $this->get('accounting.import.process');
        $data = $request->request->all();
        $errors               = $importProcess->saveForms($data);
        $result['formErrors'] = $errors;

        $response = new Response($this->get('jms_serializer')->serialize($result, 'json', $context));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    protected function isAjaxRequestValid()
    {
        $this->checkAccessToReport();
        /**
         * @var ImportStorage $importStorage
         */
        $importStorage = $this->get('accounting.import.storage');

        try {
            $data = $importStorage->getImportData();
        } catch (ImportStorageException $e) {
            return false;
        }

        if (empty($data[ImportStorage::IMPORT_MAPPING])) {
            return false;
        }

        return true;
    }
}
