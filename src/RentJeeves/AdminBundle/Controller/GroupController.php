<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CoreBundle\Sftp\SftpFileManager;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class GroupController extends BaseController
{
    /**
     * @param Group   $group
     * @param Request $request
     * @param string  $importType
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route(
     *      "import/property/createJob/{group_id}/{importType}",
     *      name="admin_create_import_job",
     *      defaults={"importType" = "property"}
     * )
     * @ParamConverter("group", class="DataBundle:Group", options={"id" = "group_id"})
     * @Method({"GET"})
     */
    public function importPropertyCreateJob(Group $group, $importType, Request $request)
    {
        if (!ImportModelType::isValid($importType)) {
            $request->getSession()->getFlashBag()->add(
                'error',
                $this->getTranslator()->trans('admin.import.lease.error.wrong_import_model_type')
            );

            return $this->redirectToRoute('admin_rj_group_list');
        }

        try {
            $extPropertyIds = $this->getImportSettingsProvider()->provideExternalPropertyIds($group);
        } catch (ImportLogicException $e) {
            $request->getSession()->getFlashBag()->add('error', $e->getMessage());

            return $this->redirectToRoute('admin_rj_group_list');
        }

        if (empty($extPropertyIds)) {
            $request->getSession()->getFlashBag()->add(
                'error',
                $this->getTranslator()->trans(
                    'admin.import_properties_job.error.empty_external_properties',
                    ['%group_name%' => $group->getName()]
                )
            );

            return $this->redirectToRoute('admin_rj_group_list');
        }

        $newImport = new Import();
        $newImport->setImportType($importType);
        $newImport->setUser($this->getUser());
        $newImport->setGroup($group);
        $newImport->setStatus(ImportStatus::RUNNING);

        $this->getEntityManager()->persist($newImport);
        $this->getEntityManager()->flush();

        foreach ($extPropertyIds as $extPropertyId) {
            $this->createJobForExternalProperty($newImport, $extPropertyId, $importType);
        }

        if ($importType === ImportModelType::PROPERTY) {
            $successMessage = $this->getTranslator()->trans(
                'admin.import_properties_job.created',
                ['%group_name%' => $group->getName()]
            );
        } elseif ($importType === ImportModelType::LEASE) {
            $successMessage = $this->getTranslator()->trans(
                'admin.import_leases_job.created',
                ['%group_name%' => $group->getName()]
            );
        }

        $request->getSession()->getFlashBag()->add('success', $successMessage);

        return $this->redirectToRoute('admin_rj_group_list');
    }

    /**
     * @param Import $import
     * @param string $externalPropertyId
     * @param string $importType
     */
    protected function createJobForExternalProperty(Import $import, $externalPropertyId, $importType)
    {
        $dependentJob = new Job(
            'renttrack:import:' . $importType,
            [
                '--import-id=' . $import->getId(),
                '--external-property-id=' . $externalPropertyId
            ]
        );

        $job = new Job(
            sprintf('renttrack:import:%s:check-status', $importType),
            ['--import-id=' . $import->getId()]
        );
        $job->addDependency($dependentJob);

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->persist($dependentJob);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Import $import
     * @param string $pathToCsv
     */
    protected function createJobForImportCsv(Import $import, $pathToCsv)
    {
        $dependentJob = new Job(
            'renttrack:import:property',
            [
                '--import-id=' . $import->getId(),
                '--path-to-file=' . $pathToCsv
            ]
        );

        $job = new Job(
            'renttrack:import:property:check-status',
            ['--import-id=' . $import->getId()]
        );
        $job->addDependency($dependentJob);

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->persist($dependentJob);
        $this->getEntityManager()->flush();
    }

    /**
     * @Route("csv_import/job/properties/{id}", name="admin_create_csv_job_for_import_properties")
     * @ParamConverter("group", class="DataBundle:Group")
     *
     * @param Request $request
     * @param Group   $group
     *
     * @return Response
     */
    public function createCsvJobForImportPropertiesAction(Request $request, Group $group)
    {
        $form = $this->createForm($this->get('form.upload_csv_file'));
        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var File $file */
            $file = $form['attachment']->getData();

            $import = new Import();
            $import->setGroup($group);
            $import->setImportType(ImportModelType::PROPERTY);
            $import->setUser($this->getUser());
            $import->setStatus(ImportStatus::RUNNING);

            $this->getEntityManager()->persist($import);
            $this->getEntityManager()->flush();

            $date = new \DateTime();
            $fileName = sprintf('/PropertyImport_%d_%s.csv', $import->getId(), $date->format('Y-m-d\TH:i:s'));
            $data = file_get_contents($file->getPathname());
            $this->getImportPropertySftpFileManager()->upload($data, $fileName);
            $this->createJobForImportCsv($import, $fileName);

            $import->setPathToFile($fileName);
            $this->getEntityManager()->flush();

            $request->getSession()->getFlashBag()->add(
                'sonata_flash_success',
                $this->getTranslator()->trans('csv.job.successfully_created')
            );

            return new RedirectResponse(
                $this->generateUrl('admin_rj_group_list', ['id' => $group->getId()])
            );
        }

        return $this->render(
            'AdminBundle:Group:createCsvJobForImportProperties.html.twig',
            [
                'group' => $group,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @return \RentJeeves\ImportBundle\PropertyImport\ImportPropertySettingsProvider
     */
    protected function getImportSettingsProvider()
    {
        return $this->get('import.property.settings_provider');
    }

    /**
     * @return SftpFileManager
     */
    protected function getImportPropertySftpFileManager()
    {
        return $this->get('import.property.sftp_file_manager');
    }
}
