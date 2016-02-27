<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\AdminBundle\Form\MatchFileType;
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
        $data = $this->get('import.reader.csv')->read($this->get('session')->get(self::FILE_PATH_KEY), 0, 3);
        $headerHash = md5(implode(array_keys($data[1])));
        $importMappingChoice = $this->getEntityManager()
            ->getRepository('RjDataBundle:ImportMappingChoice')
            ->findOneBy(['headerHash' => $headerHash, 'group' => $group]);

        $defaultMappingValue = $importMappingChoice ? $importMappingChoice->getMappingData() : [] ;
        $dataView = $this->prepareDataForCreateMapping($data);

        $form = $this->createForm(
            new MatchFileType(
                $this->get('translator'),
                $group->getImportSettings(),
                count($dataView),
                $defaultMappingValue
            )
        );
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $result = [];
            for ($i=1; $i < count($data[1])+1; $i++) {
                $nameField = MatchFileType::getFieldNameByNumber($i);
                $value = $form->get($nameField)->getData();
                if ($value === MatchFileType::EMPTY_VALUE) {
                    continue;
                }

                $result[$i] = $value;
            }

            $headerHash = md5(implode(array_keys($data[1])));

            if (!$importMappingChoice) {
                $importMappingChoice = new ImportMappingChoice();
                $importMappingChoice->setHeaderHash($headerHash);
                $importMappingChoice->setGroup($group);
            }

            $importMappingChoice->setMappingData($result);
            $this->getEntityManager()->persist($importMappingChoice);
            $this->getEntityManager()->flush($importMappingChoice);


            return new RedirectResponse(
                $this->generateUrl('admin_rj_group_edit', ['id' => $group->getId()])
            );
        }

        return $this->render(
            'AdminBundle:CsvMapping:map.html.twig',
            [
                'group' => $group,
                'data' => $dataView,
                'form'  => $form->createView()
            ]
        );
    }


    /**
     * Generate array values for view: 2 rows from csv file and choice  form field
     *
     * @param array $data
     * @return array
     */
    protected function prepareDataForCreateMapping(array $data)
    {
        $headers = array_keys($data[1]);
        $dataView = array();
        for ($i=1; $i < count($data[1])+1; $i++) {
            $dataView[] = array(
                'name' => $headers[$i-1],
                'row1' => $data[1][$headers[$i-1]],
                'row2' => (isset($data[2]) && isset($data[2][$headers[$i-1]])) ? $data[2][$headers[$i-1]] : null,
                'form' => MatchFileType::getFieldNameByNumber($i),
            );
        }

        return $dataView;
    }
}
