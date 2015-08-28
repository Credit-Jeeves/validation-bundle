<?php

namespace RentJeeves\LandlordBundle\Controller;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ImportSummary;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentLeaseFile;
use RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident;
use RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerYardi;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingMRI;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingYardi;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAbstract;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageMRI;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageYardi;
use RentJeeves\LandlordBundle\Model\Import;
use RentJeeves\LandlordBundle\Services\ImportSummaryManager;
use RentJeeves\LandlordBundle\Services\PropertyMappingManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CoreBundle\Controller\LandlordController as Controller;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;
use RentJeeves\LandlordBundle\Accounting\Import\ImportFactory;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\Yardi;
use RentJeeves\LandlordBundle\Accounting\LandlordPermission as Permission;
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
use Symfony\Component\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Monolog\Logger;

/**
 * @Route("/accounting")
 */
class AccountingController extends Controller
{
    const IMPORT = 'import';

    const EXPORT = 'export';

    /**
     * @return Logger
     */
    protected function getImportLogger()
    {
        # custom channel configured in app/config/rj/config_*.yml
        # see http://symfony.com/doc/current/cookbook/logging/channels_handlers.html#cookbook-monolog-channels-config

        return $this->get('monolog.logger.import');
    }

    protected function checkAccessToAccounting($type = self::IMPORT)
    {
        /**
         * @var $accountingPermission Permission
         */
        $accountingPermission = $this->get('landlord.permission');
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
            $validationRule = [$form['type']];
        } else {
            $validationRule = ['yardi'];
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
        $this->getImportLogger()->debug("Enter: importFileAction");

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

        $integrationType = $this->getCurrentGroup()->getHolding()->getApiIntegrationType();

        if (!$form->isValid()) {
            return [
                'form'            => $form->createView(),
                'nGroups'         => $this->getGroups()->count(),
                'source'          => $form->get('fileType')->getData(),
                'importType'      => $form->get('importType')->getData(),
                'integrationType' => $integrationType
            ];
        }

        $this->getImportLogger()->debug("Import requested. Type: " + $form['fileType']->getData());

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
        $this->getImportLogger()->debug("Enter: matchFileAction");

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
            return [
                'error' => $e->getMessage()
            ];
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

        return [
            'error'        => false,
            'data'         => $dataView,
            'form'         => $form
        ];
    }

    /**
     * @Route(
     *     "/import/summary/report/{publicId}",
     *     name="import_summary_report",
     *     options={"expose"=true}
     * )
     * @ParamConverter(
     *      "importSummary",
     *      class="RjDataBundle:ImportSummary"
     * )
     */
    public function summaryReportAction(ImportSummary $importSummary)
    {
        return $this->render(
            'LandlordBundle:Accounting:summaryReport.html.twig',
            ['report' => $importSummary]
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
        $this->getImportLogger()->debug("Enter: importAction");

        $this->checkAccessToAccounting();
        try {
            /** @var ImportFactory $importFactory */
            $importFactory = $this->get('accounting.import.factory');
            $storage = $importFactory->getStorage();
            $storage->clearDataBeforeReview();
            $mapping = $importFactory->getMapping();
            if ($mapping->isNeedManualMapping()) {
                $storage->getImportData();
            }
        } catch (\Exception $e) {
            return $this->redirect($this->generateUrl('accounting_import_file'));
        }

        $handler = $importFactory->getHandler();
        $import = new Import();
        $import->setContract(new Contract());
        $formNewUserWithContract = $handler->getCreateUserAndCreateContractForm();
        $formContract = $handler->getContractForm();
        $formContractFinish = $handler->getContractFinishForm();

        return array(
            'formNewUserWithContract' => $formNewUserWithContract->createView(),
            'formContract'            => $formContract->createView(),
            'formContractFinish'      => $formContractFinish->createView(),
            'importStorage'           => $storage,
            'importMapping'           => $mapping,
            //Make it string because it's var for js and I want boolean
            'isMultipleProperty'      => ($storage->isMultipleProperty()) ? "true" : "false",
            'importOnlyException'     => ($storage->isOnlyException()) ? "true" : "false",
            'supportEmail'            => $this->container->getParameter('support_email'),
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
        $this->getImportLogger()->debug('Enter: getRowsAction');

        $result = [
            'error'   => false,
            'message' => '',
        ];

        if (!$this->isAjaxRequestValid()) {
            $this->getImportLogger()->error($this->get('translator')->trans('import.error.access'));

            $result['error'] = true;
            $result['message'] = $this->get('translator')->trans('import.error.access');

            return new JsonResponse($result);
        }

        /** @var ImportFactory $importFactory */
        $importFactory = $this->get('accounting.import.factory');

        $this->getImportLogger()->debug("Getting Import Storage");
        $storage = $importFactory->getStorage();
        $this->getImportLogger()->debug("Getting Import Mapping");
        $mapping = $importFactory->getMapping();

        $this->getImportLogger()->debug("Import ready!");

        // convert from string to boolean
        $newRows = filter_var($request->request->get('newRows', false), FILTER_VALIDATE_BOOLEAN);

        $this->getImportLogger()->debug(
            sprintf("Import fetching %s rows at offset %s", ImportHandler::ROW_ON_PAGE, $storage->getOffsetStart())
        );

        if ($newRows) {
            $storage->setOffsetStart($storage->getOffsetStart() + ImportHandler::ROW_ON_PAGE);
        }

        $collection = [];

        $handler = $importFactory->getHandler();
        $total = $mapping->getTotalContent();

        $this->getImportLogger()->debug("Getting total of " . $total);

        if ($total > 0) {
            $collection = $handler->getCurrentCollectionImportModel();
        } elseif (!$storage->isOnlyException()) {
            $storage->clearSession();
        }

        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups('RentJeevesImport');
        $importSummaryManager = $handler->getReport();
        $importSummaryManager->setTotal($total);

        $result['rows'] = $collection;
        $result['total'] = $total;
        $result['importSummaryPublicId'] = $importSummaryManager->getReportPublicId();

        $this->getImportLogger()->debug("Reading from file...");
        $response = new Response($this->get('jms_serializer')->serialize($result, 'json', $context));
        $response->headers->set('Content-Type', 'application/json');
        $this->getImportLogger()->debug("Sending response...");

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
        /** @var $importFactory ImportFactory  */
        $importFactory = $this->get('accounting.import.factory');
        $handler = $importFactory->getHandler();
        $data = $request->request->all();

        // Hydrate contract and sub-object model from form data.
        $result['formErrors'] = $handler->saveForms($data);
        $result['rows'] = $collection = $handler->getCurrentCollectionImportModel();

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
     *     "/import/property_mapping/yardi",
     *     name="accounting_import_property_mapping_yardi",
     *     options={"expose"=true}
     * )
     */
    public function getMappedPropertiesYardi()
    {
        /** @var $importFactory ImportFactory */
        $importFactory = $this->get('accounting.import.factory');
        /** @var StorageYardi $storage */
        $storage = $importFactory->getStorage();

        $mappedProperties = [];

        if ($storage->getImportLoaded() === false) {
            /** @var $propertyMappingManager PropertyMappingManager */
            $propertyMappingManager = $this->get('property_mapping.manager');

            if (ImportType::SINGLE_PROPERTY === $storage->getImportType()) {
                /** @var PropertyMapping[] $mappedProperties */
                $mappedProperties[] = $propertyMappingManager->createPropertyMapping(
                    $storage->getImportPropertyId(),
                    $storage->getImportExternalPropertyId()
                );
            } else {
                $mappedProperties = $propertyMappingManager->getMappedProperties();
            }

        }

        $response = new Response($this->get('jms_serializer')->serialize($mappedProperties, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @param int $propertyMappingId
     *
     * @Route(
     *     "/import/residents/yardi/{propertyMappingId}",
     *     name="accounting_import_residents_yardi",
     *     options={"expose"=true}
     * )
     *
     * @return Response
     */
    public function getResidentsYardi($propertyMappingId)
    {
        /** @var $importFactory ImportFactory */
        $importFactory = $this->get('accounting.import.factory');
        /** @var StorageYardi $storage */
        $storage = $importFactory->getStorage();

        $residents = [];

        if ($storage->getImportLoaded() === false) {
            /** @var MappingYardi $mapping */
            $mapping = $importFactory->getMapping();
            /** @var Holding $holding */
            $holding = $this->getUser()->getHolding();
            /** @var $propertyMapping PropertyMapping */
            $propertyMapping = $this->getEntityManager()->getRepository('RjDataBundle:PropertyMapping')->findOneBy([
                'id' => $propertyMappingId,
                'holding' => $holding->getId()
            ]);
            if ($propertyMapping) {
                $residents = array_merge(
                    $mapping->getResidents($holding, $propertyMapping->getProperty()),
                    $residents
                );
            }
        }
        $em = $this->getEntityManager();
        if (!$em->getConnection()->isConnected()) {
            $em->getConnection()->close();
            $em->getConnection()->connect();
        }
        $handler = $importFactory->getHandler();
        $handler->getReport()->setTotal(count($residents));
        $response = new Response($this->get('jms_serializer')->serialize($residents, 'json'));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    protected function getBaseResidents()
    {
        $importFactory = $this->get('accounting.import.factory');
        $mapping = $importFactory->getMapping();
        /** @var StorageAbstract $storage */
        $storage = $importFactory->getStorage();
        $propertyMappingManager = $this->get('property_mapping.manager');

        if (!$storage->isMultipleProperty()) {
            $propertyMappingManager->createPropertyMapping(
                $storage->getImportPropertyId(),
                $storage->getImportExternalPropertyId()
            );
        }

        $residents = $mapping->getResidents($storage->getImportExternalPropertyId());
        $result = $storage->saveToFile($residents);

        if ($storage->isOnlyException()) {
            $handler = $importFactory->getHandler();
            $handler->updateMatchedContracts();
        }

        $response = new JsonResponse();
        $response->setStatusCode(($result) ? 200 : 400);

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
        return $this->getBaseResidents();
    }

    /**
     * @Route(
     *     "/import/residents/mri",
     *     name="accounting_import_residents_mri",
     *     options={"expose"=true}
     * )
     */
    public function getResidentsMri()
    {
        $importFactory = $this->get('accounting.import.factory');
        /** @var MappingMRI $mapping */
        $mapping = $importFactory->getMapping();
        /** @var StorageMRI $storage */
        $storage = $importFactory->getStorage();
        $nextPageLink = $this->get('request')->request->get('nextPageLink');

        if (empty($nextPageLink)) {
            $residents = $mapping->getResidents($storage->getImportExternalPropertyId());
        } else {
            $residents = $mapping->getResidentsByNextPageLink($nextPageLink);
        }

        $result = $storage->saveToFile($residents);
        $newNextPageLink = $mapping->getNextPageLink();
        //We need update matched contracts only after download all of them, that's why check var newNextPageLink
        if ($storage->isOnlyException() && empty($newNextPageLink)) {
            $handler = $importFactory->getHandler();
            $handler->updateMatchedContracts();
        }

        $response = new JsonResponse(['nextPageLink' => $newNextPageLink]);
        $response->setStatusCode((!empty($nextPageLink) || $result) ? 200 : 400);

        return $response;
    }

    /**
     * @Route(
     *     "/import/residents/amsi",
     *     name="accounting_import_residents_amsi",
     *     options={"expose"=true}
     * )
     */
    public function getResidentsAmsi()
    {
        return $this->getBaseResidents();
    }

    /**
     * @Route(
     *     "/import/resident/yardi/{propertyMappingId}/{isLast}",
     *     name="accounting_import_resident_data_yardi",
     *     options={"expose"=true}
     * )
     *
     * @param int $propertyMappingId
     * @param int $isLast
     * @return Response
     * @throws Exception
     */
    public function getResidentData($propertyMappingId, $isLast = 0)
    {
        $holding = $this->getUser()->getHolding();
        $request = $this->get('request');
        $residentPostData = $request->request->get('resident');
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');
        $classResident = 'RentJeeves\ExternalApiBundle\Services\Yardi\Soap\ResidentsResident';
        /** @var ResidentsResident $resident */
        $resident = $serializer->deserialize(
            $residentPostData,
            $classResident,
            'array'
        );

        if (!$resident instanceof ResidentsResident) {
            throw new Exception("Invalid post data, can't be converted to {$classResident}");
        }

        /** @var $importFactory ImportFactory */
        $importFactory = $this->get('accounting.import.factory');
        /** @var MappingYardi $mapping */
        $mapping = $importFactory->getMapping();
        /** @var StorageYardi $storage */
        $storage = $importFactory->getStorage();
        $em = $this->getDoctrine()->getManager();

        try {
            /*
             * Search PropertyMapping by id with holding
             * for be sure that we can work only with property belongs to our holding
             */
            /** @var PropertyMapping $propertyMapping */
            $propertyMapping = $em->getRepository('RjDataBundle:PropertyMapping')->findOneBy([
                'id' => $propertyMappingId,
                'holding' => $holding->getId()
            ]);
            if (!$propertyMapping) {
                throw new \RuntimeException('PropertyMapping is not defined.');
            }

            $residentLeaseFile = $mapping->getContractData($holding, $propertyMapping->getProperty(), $resident);
            $storage->setImportPropertyId($propertyMapping->getProperty()->getId());
            $storage->saveToFile($residentLeaseFile, $resident);

            if (!$residentLeaseFile instanceof ResidentLeaseFile) {
                $responseData = array('result' => false);
            } else {
                $responseData = array('result' => true);
            }
        } catch (Exception $e) {
            $this->get('fp_badaboom.exception_catcher')->handleException($e);
            $responseData = array('result' => false);
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

        $response = new Response($this->get('jms_serializer')->serialize($responseData, 'json'));
        $response->headers->set('Content-Type', 'application/json');
        $statusCode = ($responseData['result']) ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;
        $response->setStatusCode($statusCode);

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
        /** @var ImportFactory $importFactory */
        $importFactory = $this->get('accounting.import.factory');
        $handler = $importFactory->getHandler();
        $result = $handler->updateMatchedContracts();
        $response = new JsonResponse(
            ['success' => $result]
        );

        $statusCode = ($result) ? 200 : 400;
        $response->setStatusCode($statusCode);

        return $response;
    }
}
