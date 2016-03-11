<?php

namespace RentJeeves\AdminBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use RentJeeves\AdminBundle\Form\MatchFileType;
use RentJeeves\ComponentBundle\FileReader\CsvFileReaderImport;
use RentJeeves\CoreBundle\Helpers\HashHeaderCreator;
use RentJeeves\DataBundle\Entity\ImportMappingChoice;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Form;

class CsvMappingCreator
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CsvFileReaderImport
     */
    protected $csvReader;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $csvPath;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $csvDataCache = [];

    /**
     * @param EntityManager $em
     * @param CsvFileReaderImport $csvReader
     * @param Translator $translator
     * @param FormFactory $formFactory
     */
    public function __construct(
        EntityManager $em,
        CsvFileReaderImport $csvReader,
        Translator $translator,
        FormFactory $formFactory
    ) {
        $this->em = $em;
        $this->csvReader = $csvReader;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
    }

    /**
     * @param string $csvPath
     */
    public function setCsvPath($csvPath)
    {
        if (!file_exists($csvPath)) {
            new \LogicException('Csv file doesn\'t exist ' . $csvPath);
        }

        $this->csvPath = $csvPath;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return count($this->errors) > 0;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param Group $group
     * @return Form|null
     */
    public function createForm(Group $group)
    {
        $this->validate();

        if ($this->isError()) {
            return null;
        }

        $importMappingChoice = $this->getImportMappingChoice($group);
        $defaultMappingValue = $importMappingChoice ? $importMappingChoice->getMappingData() : [] ;
        $dataView = $this->getViewData();

        return $this->formFactory->create(
            new MatchFileType(
                $this->translator,
                $group->getImportSettings(),
                count($dataView),
                $defaultMappingValue
            ),
            null,
            []
        );
    }

    /**
     * @param Form $form
     * @param Group $group
     */
    public function saveForm(Form $form, Group $group)
    {
        $data = $this->getCsvData();
        $importMappingChoice = $this->getImportMappingChoice($group);

        $result = [];
        for ($i=1; $i < count($data[1])+1; $i++) {
            $nameField = MatchFileType::getFieldNameByNumber($i);
            $value = $form->get($nameField)->getData();
            if ($value === MatchFileType::EMPTY_VALUE) {
                continue;
            }

            $result[$i] = $value;
        }

        if (!$importMappingChoice) {
            $importMappingChoice = new ImportMappingChoice();
            $importMappingChoice->setHeaderHash($this->generateHash($data));
            $importMappingChoice->setGroup($group);
        }

        $importMappingChoice->setMappingData($result);

        $this->em->persist($importMappingChoice);
        $this->em->flush($importMappingChoice);
    }

    /**
     * Generate array values for view: 2 rows from csv file and choice  form field
     *
     * @return array
     */
    public function getViewData()
    {
        $data = $this->getCsvData();
        $headers = array_keys($data[1]);
        $dataView = [];
        for ($i=1; $i < count($data[1])+1; $i++) {
            $dataView[] = [
                'name' => $headers[$i-1],
                'row1' => $data[1][$headers[$i-1]],
                'row2' => (isset($data[2]) && isset($data[2][$headers[$i-1]])) ? $data[2][$headers[$i-1]] : null,
                'form' => MatchFileType::getFieldNameByNumber($i),
            ];
        }

        return $dataView;
    }

    /**
     * @return array
     */
    protected function getCsvData()
    {
        if (empty($this->csvPath)) {
            return [];
        }

        if (array_key_exists($this->csvPath, $this->csvDataCache)) {
            return $this->csvDataCache[$this->csvPath];
        }

        $data = $this->csvReader->read($this->csvPath, 0, 3);
        $this->csvDataCache[$this->csvPath] = $data;

        return $data;
    }

    /**
     * @param Group $group
     * @return null|ImportMappingChoice
     */
    protected function getImportMappingChoice(Group $group)
    {
        return $this->em->getRepository('RjDataBundle:ImportMappingChoice')
            ->findOneBy(
                [
                    'headerHash' => $this->generateHash($this->getCsvData()),
                    'group' => $group
                ]
            );
    }

    /**
     * @param array $data
     * @return string
     */
    protected function generateHash($data)
    {
        return HashHeaderCreator::createHashHeader(array_keys($data[1]));
    }

    /**
     * Validate required data for work this service
     */
    protected function validate()
    {
        $this->errors = [];

        if (empty($this->csvPath)) {
            $this->errors[] = $this->translator->trans('csv.mapping.error.file_empty');
        }

        $csvData = $this->getCsvData();

        if (count($csvData) < 0 && !isset($data[1])) {
            $this->errors[] = $this->translator->trans('csv.file.too.small');
        }
    }
}
