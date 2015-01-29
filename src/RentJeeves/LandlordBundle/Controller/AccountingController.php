<?php

namespace RentJeeves\LandlordBundle\Controller;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerYardi;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingResman;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageResman;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\LandlordBundle\Services\PropertyMappingManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;
use RentJeeves\LandlordBundle\Accounting\Import\ImportFactory;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\Yardi;
use RentJeeves\LandlordBundle\Accounting\AccountingPermission as Permission;
use RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerAbstract as ImportHandler;
use RentJeeves\LandlordBundle\Exception\ImportMappingException;
use RentJeeves\LandlordBundle\Exception\ImportStorageException;
use RentJeeves\LandlordBundle\Form\ExportType;
use RentJeeves\LandlordBundle\Form\ImportFileAccountingType;
use RentJeeves\LandlordBundle\Form\ImportMatchFileType;
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
            $validationRule = array('yardi');
        }

        $group = $this->get('core.session.landlord')->getGroup();
        $formBaseOrder = $this->createForm(
            new ExportType($this->getUser(), $group, $validationRule)
        );

        $formBaseOrder->handleRequest($this->get('request'));
        if ($formBaseOrder->isValid()) {

            $formData = $formBaseOrder->getData();
            $formData['landlord'] = $this->get('core.session.landlord');
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
            }
            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('export.no_data'));
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
            new ImportFileAccountingType(
                $this->getUser()->getIsSuperAdmin(),
                $this->getCurrentGroup(),
                $this->getDoctrine()->getManager()
            )
        );

        $form->handleRequest($this->get('request'));
        /** @var ImportFactory $importFactory */
        $importFactory = $this->get('accounting.import.factory');
        $importFactory->clearSessionAllImports();

        if (($accounting = $this->getCurrentGroup()->getHolding()->getAccountingSettings())) {
            $integrationType = $accounting->getApiIntegration();
        } else {
            $integrationType = null;
        }

        if (!$form->isValid()) {
            return array(
                'form'            => $form->createView(),
                'nGroups'         => $this->getGroups()->count(),
                'source'          => $form->get('fileType')->getData(),
                'importType'      => $form->get('importType')->getData(),
                'integrationType' => $integrationType
            );
        }

        $importStorage = $importFactory->getStorage($form['fileType']->getData());
        $importStorage->setImportData($form);
        $importStorage->setStorageType(
            $importFactory->getImportType($form['fileType']->getData())
        );

        return $this->redirect(
            $this->generateUrl('accounting_match_file')
        );
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
             * @var $importFactory ImportFactory
             */
            $importFactory = $this->get('accounting.import.factory');
            $importStorage = $importFactory->getStorage();
            $importMapping = $importFactory->getMapping();

            if ($importMapping->isNeedManualMapping()) {
                $importStorage->getImportData();
                $data = $importMapping->getDataForMapping();
            } else {
                return $this->redirect($this->generateUrl('accounting_import'));
            }
        } catch (ImportStorageException $e) {
            return $this->redirect($this->generateUrl('accounting_import_file'));
        } catch (ImportMappingException $e) {
            return array(
                'error' => $e->getMessage()
            );
        }

        $group = $this->get('core.session.landlord')->getGroup();
        $headerHash = $importMapping::getHeaderFileHash($data);
        $importMappingChoice = $importMapping->getSelectedImportMapping($headerHash, $group);
        $defaultMappingValue = $importMappingChoice ? $importMappingChoice->getMappingData() : [] ;

        $dataView = $importMapping->prepareDataForCreateMapping($data);
        $form = $this->createForm(
            new ImportMatchFileType(
                count($dataView),
                $this->get('translator'),
                $importStorage,
                $defaultMappingValue
            )
        );
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $importMapping->setupMapping($form, $data, $group);
            return $this->redirect($this->generateUrl('accounting_import'));
        }

        $form = $form->createView();

        return array(
            'error'        => false,
            'data'         => $dataView,
            'form'         => $form
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
        try {
            /**
             * @var $importFactory ImportFactory
             */
            $importFactory = $this->get('accounting.import.factory');
            $storage = $importFactory->getStorage();
            $storage->clearDataBeforeReview();
            $mapping = $importFactory->getMapping();
            if ($mapping->isNeedManualMapping()) {
                $storage->getImportData();
            }
        } catch (ImportStorageException $e) {
            return $this->redirect($this->generateUrl('accounting_import_file'));
        }

        $handler = $importFactory->getHandler();
        $import = new Import();
        $import->setContract(new Contract());
        $formNewUserWithContract = $handler->getCreateUserAndCreateContractForm(
            $import
        );
        $formContract = $handler->getContractForm(
            $import
        );
        $formContractFinish = $handler->getContractFinishForm();

        return array(
            'formNewUserWithContract' => $formNewUserWithContract->createView(),
            'formContract'            => $formContract->createView(),
            'formContractFinish'      => $formContractFinish->createView(),
            'importStorage'           => $storage,
            'importMapping'           => $mapping,
            //Make it string because it's var for js and I want boolean
            'isMultipleProperty'      => ($storage->isMultipleProperty())? "true" : "false",
            'importOnlyException'     => ($storage->isOnlyException())? "true" : "false",
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
         * @var $importFactory ImportFactory
         */
        $importFactory = $this->get('accounting.import.factory');
        $storage = $importFactory->getStorage();
        $mapping = $importFactory->getMapping();

        $newRows = filter_var($request->request->get('newRows', false), FILTER_VALIDATE_BOOLEAN);

        if ($newRows) {
            $storage->setOffsetStart($storage->getOffsetStart() + ImportHandler::ROW_ON_PAGE);
        }

        $rows = array();

        $handler = $importFactory->getHandler();
        $total = $mapping->getTotal();

        if ($total > 0) {
            $rows = $handler->getImportModelCollection();
        } else {
            $storage->clearSession();
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
         * @var $importFactory ImportFactory
         */
        $importFactory = $this->get('accounting.import.factory');
        $handler = $importFactory->getHandler();
        $data = $request->request->all();
        $result['formErrors'] = $handler->saveForms($data);

        $response = new Response($this->get('jms_serializer')->serialize($result, 'json', $context));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    protected function isAjaxRequestValid()
    {
        $this->checkAccessToAccounting();
        /**
         * @var $importFactory ImportFactory
         */
        $importFactory = $this->get('accounting.import.factory');
        $importStorage = $importFactory->getStorage();
        return $importStorage->isValid();
    }

    /**
     * @Route(
     *     "/import/residents/yardi",
     *     name="accounting_import_residents_yardi",
     *     options={"expose"=true}
     * )
     */
    public function getResidentsYardi()
    {
        $importFactory = $this->get('accounting.import.factory');
        $mapping = $importFactory->getMapping();
        $storage = $importFactory->getStorage();
        /**
         * @var $propertyMappingManager PropertyMappingManager
         */
        $propertyMappingManager = $this->get('property_mapping.manager');
        $propertyMapping = $propertyMappingManager->createPropertyMapping(
            $storage->getImportPropertyId(),
            $storage->getImportExternalPropertyId()
        );
        $holding = $this->getUser()->getHolding();

        if ($storage->getImportLoaded() === false) {
            $residents = $mapping->getResidents($holding, $propertyMapping->getProperty());
            $residents = array_values($residents);
        } else {
            $residents = array();
        }

        $response = new Response($this->get('jms_serializer')->serialize($residents, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/import/residents/resman",
     *     name="accounting_import_residents_resman",
     *     options={"expose"=true}
     * )
     */
    public function getResidentsResMan()
    {
        $importFactory = $this->get('accounting.import.factory');
        /** @var $mapping MappingResman */
        $mapping = $importFactory->getMapping();
        /** @var $storage StorageResman */
        $storage = $importFactory->getStorage();
        /** @var $propertyMappingManager PropertyMappingManager  */
        $propertyMappingManager = $this->get('property_mapping.manager');
        $propertyMapping = $propertyMappingManager->createPropertyMapping(
            $storage->getImportPropertyId(),
            $storage->getImportExternalPropertyId()
        );

        $residents = $mapping->getResidents($propertyMapping->getExternalPropertyId());
        $result = $storage->saveToFile($residents);

        $response = new JsonResponse();
        $response->setStatusCode(($result) ? 200 : 400);

        return $response;
    }

    /**
     * @Route(
     *     "/import/resident/yardi/{residentId}/{isLast}",
     *     name="accounting_import_resident_data_yardi",
     *     options={"expose"=true}
     * )
     */
    public function getResidentData($residentId, $isLast = 0)
    {
        $holding = $this->getUser()->getHolding();
        $request = $this->get('request');
        $moveOutDate = $request->request->get('moveOutDate');
        $paymentAccepted = $request->request->get('paymentAccepted');


        /**
         * @var $importFactory ImportFactory
         */
        $importFactory = $this->get('accounting.import.factory');
        $mapping = $importFactory->getMapping();
        $storage = $importFactory->getStorage();
        $em = $this->getDoctrine()->getManager();
        /**
         * @var $propertyMapping PropertyMapping
         */
        $propertyMapping = $em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
            array(
                'property'              => $storage->getImportPropertyId(),
                'holding'               => $holding->getId()
            )
        );
        try {
            $residentLeaseFile = $mapping->getContractData($holding, $propertyMapping->getProperty(), $residentId);
            $storage->saveToFile($residentLeaseFile, $residentId, $moveOutDate, $paymentAccepted);

            if (!$residentLeaseFile instanceof ResidentLeaseFile) {
                $result = false;
            } else {
                $result = true;
            }
        } catch (Exception $e) {
            $result = false;
        }

        if ($isLast) {
            $storage->setImportLoaded(true);
        }

        if ($storage->isOnlyException()) {
            /**
             * @var $handler HandlerYardi
             */
            $handler = $importFactory->getHandler();
            $handler->updateMatchedContracts();
        }

        $response = new Response($result);
        $response->setStatusCode(($result) ? 200 : 400);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/import/update/matched/contracts/csv",
     *     name="updateMatchedContractsCsv",
     *     options={"expose"=true}
     * )
     */
    public function updateMatchedContractsCsv()
    {
        /**
         * @var $importFactory ImportFactory
         */
        $importFactory = $this->get('accounting.import.factory');
        $handler = $importFactory->getHandler();
        $result = $handler->updateMatchedContracts();

        $response = new JsonResponse(
            array('success' => $result)
        );

        $statusCode = ($result) ? 200 : 400;
        $response->setStatusCode($statusCode);

        return $response;
    }
}
