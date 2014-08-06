<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitMapping;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;
use RentJeeves\LandlordBundle\Accounting\ImportMapping;
use RentJeeves\LandlordBundle\Accounting\ImportProcess;
use RentJeeves\LandlordBundle\Accounting\ImportStorage;
use RentJeeves\LandlordBundle\Accounting\AccountingPermission as Permission;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;
use RentJeeves\LandlordBundle\Form\ExportType;
use RentJeeves\LandlordBundle\Form\ImportFileAccountingType;
use RentJeeves\LandlordBundle\Form\ImportMatchFileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

/**
 * @Route("/accounting")
 */
class AccountingController extends Controller
{
    const IMPORT = 'import';

    const EXPORT = 'export';

    protected function checkAccessToAccounting($type = self::IMPORT)
    {
        /**
         * @var $accountingPermission Permission
         */
        $accountingPermission = $this->get('accounting.permission');
        if (!$accountingPermission->hasAccessToAccountingTab()) {
            throw new Exception("Don't have access");
        }

        switch ($type) {
            case self::IMPORT:
                $methodName = 'hasAccessToImport';
                break;
            case self::EXPORT:
                $methodName = 'hasAccessToExport';
                break;
            default:
                throw new Exception("Don't have access");
        }

        if (!$accountingPermission->$methodName()) {
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
        $this->checkAccessToAccounting(self::EXPORT);
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

            $formData = $formBaseOrder->getData();
            $formData['group'] = $group;
            $accounting = $this->get('accounting.export');
            /** @var ExportReport $report */
            $report = $accounting->getReport($formData);

            if ($content = $report->getContent($formData)) {
                $response = new Response();
                $response->setContent($content);
                $response->headers->set('Cache-Control', 'private');
                $response->headers->set('Content-Type', $report->getContentType());
                $response->headers->set('Content-Disposition', 'attachment; filename=' . $report->getFilename());

                return $response;
            } else {
                $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('export.no.data'));
            }
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
        $this->checkAccessToAccounting();
        $form = $this->createForm(
            new ImportFileAccountingType($this->getCurrentGroup())
        );

        $form->handleRequest($this->get('request'));
        if (!$form->isValid()) {
            return array(
                'form'      => $form->createView(),
                'nGroups' => $this->getGroups()->count(),
            );
        }

        $file = $form['attachment']->getData();
        $property = $form['property']->getData();
        $textDelimiter = $form['textDelimiter']->getData();
        $fieldDelimiter = $form['fieldDelimiter']->getData();
        $dateFormat = $form['dateFormat']->getData();
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
        if ($property instanceof Property) {
            $importStorage->setPropertyId($property->getId());
            $importStorage->setIsMultipleProperty(false);
        } else {
            $importStorage->setIsMultipleProperty(true);
        }
        $importStorage->setDateFormat($dateFormat);

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
        $this->checkAccessToAccounting();
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
            return $this->redirect($this->generateUrl('accounting_import_file'));
        } catch (ImportMappingException $e) {
            return array(
                'error' => $e->getMessage()
            );
        }

        $dataView = $importMapping->prepareDataForCreateMapping($data);
        $form = $this->createForm(
            new ImportMatchFileType(
                count($dataView),
                $this->get('translator'),
                $this->get('accounting.import.storage')
            )
        );
        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            $importMapping->setupMapping($form, $data);
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
        $this->checkAccessToAccounting();
        /**
         * @var ImportStorage $importStorage
         */
        $importStorage = $this->get('accounting.import.storage');
        try {
            $data = $importStorage->getImportData();
        } catch (ImportStorageException $e) {
            return $this->redirect($this->generateUrl('accounting_import_file'));
        }

        if (empty($data[ImportStorage::IMPORT_MAPPING])) {
            return $this->redirect($this->generateUrl('accounting_import_file'));
        }
        /**
         * @var $importProcess ImportProcess
         */
        $importProcess = $this->get('accounting.import.process');
        $formNewUserWithContract = $importProcess->getCreateUserAndCreateContractForm(
            new ResidentMapping(),
            new UnitMapping(),
            new Unit()
        );
        $formContract = $importProcess->getContractForm(
            new Tenant(),
            new ResidentMapping(),
            new UnitMapping(),
            new Unit()
        );
        $formContractFinish = $importProcess->getContractFinishForm();

        return array(
            'formNewUserWithContract' => $formNewUserWithContract->createView(),
            'formContract'            => $formContract->createView(),
            'formContractFinish'      => $formContractFinish->createView(),
            //Make it string because it's var for js and I want boolean
            'isMultipleProperty'      => ($importStorage->isMultipleProperty())? "true" : "false",
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
            $rows = $importProcess->getImportModelCollection();
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
        $this->checkAccessToAccounting();
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
        $result['formErrors'] = $importProcess->saveForms($data);

        $response = new Response($this->get('jms_serializer')->serialize($result, 'json', $context));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    protected function isAjaxRequestValid()
    {
        $this->checkAccessToAccounting();
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
