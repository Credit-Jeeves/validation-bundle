<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\AdminBundle\Form\MatchFileType;
use RentJeeves\AdminBundle\Services\CsvMappingCreator;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CsvMappingController extends BaseController
{
    const FILE_PATH_KEY = 'admin_csv_mapping_file_path';

    /**
     * @Route("upload/csv/{id}", name="admin_upload_csv")
     * @ParamConverter("group", class="DataBundle:Group")
     *
     * @param Request $request
     * @return Response
     */
    public function fileAction(Request $request, Group $group)
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
            'AdminBundle:CsvMapping:file.html.twig',
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
                'error',
                implode(',', $csvMappingCreator->getErrors())
            );
            var_dump(implode(',', $csvMappingCreator->getErrors()));exit;
            //return new RedirectResponse(
            //    $this->generateUrl('admin_rj_group_edit', ['id' => $group->getId()])
            //);
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            $csvMappingCreator->saveForm($form, $group);
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->getTranslator()->trans('csv.mapping.success')
            );

            return new RedirectResponse(
                $this->generateUrl('admin_rj_group_edit', ['id' => $group->getId()])
            );
        }

        return $this->render(
            'AdminBundle:CsvMapping:map.html.twig',
            [
                'group' => $group,
                'data' => $csvMappingCreator->getViewData(),
                'form'  => $form->createView()
            ]
        );
    }



}
