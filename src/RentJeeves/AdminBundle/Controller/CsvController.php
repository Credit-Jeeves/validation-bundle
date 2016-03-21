<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\AdminBundle\Services\CsvMappingCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;

class CsvController extends BaseController
{
    const FILE_PATH_KEY = 'admin_csv_mapping_file_path';

    /**
     * @Route("upload/csv/mapping/{id}", name="admin_upload_csv_mapping")
     * @ParamConverter("group", class="DataBundle:Group")
     *
     * @param Request $request
     * @param Group   $group
     *
     * @return Response
     */
    public function uploadFileForMappingAction(Request $request, Group $group)
    {
        $form = $this->createForm($this->get('form.upload_csv_file'));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['attachment']->getData();
            $tmpDir = sys_get_temp_dir();
            $newFileName = uniqid() . '.csv';
            $file->move($tmpDir, $newFileName);
            $this->get('session')->set(
                self::FILE_PATH_KEY,
                sprintf('%s%s%s', $tmpDir, DIRECTORY_SEPARATOR, $newFileName)
            );

            return new RedirectResponse(
                $this->generateUrl('admin_map_csv', ['id' => $group->getId()])
            );
        }

        return $this->render(
            'AdminBundle:Csv:uploadFileForMapping.html.twig',
            [
                'group' => $group,
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("map/csv/{id}", name="admin_map_csv")
     * @ParamConverter("group", class="DataBundle:Group")
     *
     * @param Request $request
     * @return Response
     */
    public function mapAction(Request $request, Group $group)
    {
        /** @var CsvMappingCreator $csvMappingCreator */
        $csvMappingCreator = $this->get('csv.mapping.creator');
        $csvMappingCreator->setCsvPath($this->get('session')->get(self::FILE_PATH_KEY));
        $form = $csvMappingCreator->createForm($group);

        if ($csvMappingCreator->isError()) {
            $request->getSession()->getFlashBag()->add(
                'sonata_flash_error',
                implode(',', $csvMappingCreator->getErrors())
            );

            return new RedirectResponse(
                $this->generateUrl('admin_upload_csv', ['id' => $group->getId()])
            );
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            $csvMappingCreator->saveForm($form, $group);
            $request->getSession()->getFlashBag()->add(
                'sonata_flash_success',
                $this->getTranslator()->trans('csv.mapping.success')
            );

            return new RedirectResponse(
                $this->generateUrl('admin_rj_group_edit', ['id' => $group->getId()])
            );
        }

        return $this->render(
            'AdminBundle:Csv:map.html.twig',
            [
                'group' => $group,
                'data' => $csvMappingCreator->getViewData(),
                'form'  => $form->createView()
            ]
        );
    }

    /**
     * @Route("create/csv/job/for/import/properties/{id}", name="admin_create_csv_job_for_import_properties")
     * @ParamConverter("group", class="DataBundle:Group")
     *
     * @param Request $request
     * @param Group   $group
     *
     * @return Response
     */
    public function createJobForImportPropertiesAction(Request $request, Group $group)
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
                $this->generateUrl('admin_rj_group_edit', ['id' => $group->getId()])
            );
        }

        return $this->render(
            'AdminBundle:Csv:createJobForImportProperties.html.twig',
            [
                'group' => $group,
                'form' => $form->createView()
            ]
        );
    }
}
