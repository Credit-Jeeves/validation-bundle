<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("import/property/createJob/{group_id}", name="admin_create_import_property_job")
     * @ParamConverter("group", class="DataBundle:Group", options={"id" = "group_id"})
     * @Method({"GET"})
     */
    public function importPropertyCreateJob(Group $group, Request $request)
    {
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
        } else {
            $newImport = new Import();
            $newImport->setImportType(ImportModelType::PROPERTY);
            $newImport->setUser($this->getUser());
            $newImport->setGroup($group);
            $newImport->setStatus(ImportStatus::RUNNING);

            $this->getEntityManager()->persist($newImport);
            $this->getEntityManager()->flush();

            foreach ($extPropertyIds as $extPropertyId) {
                $this->createJobForExternalProperty($newImport, $extPropertyId);
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                $this->getTranslator()->trans(
                    'admin.import_properties_job.created',
                    ['%group_name%' => $group->getName()]
                )
            );
        }

        return $this->redirectToRoute('admin_rj_group_list');
    }

    /**
     * @param Import $import
     * @param string $externalPropertyId
     */
    protected function createJobForExternalProperty(Import $import, $externalPropertyId)
    {
        $dependentJob = new Job(
            'renttrack:import:property',
            [
                '--import-id=' . $import->getId(),
                '--external-property-id=' . $externalPropertyId
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
            $file = $form['attachment']->getData();
            $tmpDir = sys_get_temp_dir();
            $newFileName = uniqid() . '.csv';
            $file->move($tmpDir, $newFileName);
            $filePath = sprintf('%s%s%s', $tmpDir, DIRECTORY_SEPARATOR, $newFileName);
            $import = new Import();
            $import->setGroup($group);
            $import->setImportType(ImportModelType::PROPERTY);
            $import->setUser($this->getUser());
            $import->setStatus(ImportStatus::RUNNING);
            $this->getEntityManager()->persist($import);
            $this->getEntityManager()->flush();

            $job = new Job(
                'renttrack:import:property',
                ['--path-to-file=' . $filePath, '--import-id=' . $import->getId()]
            );

            $this->getEntityManager()->persist($job);
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
}
