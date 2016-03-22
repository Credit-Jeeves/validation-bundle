<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\ImportBundle\Exception\ImportException;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Service`s name "import.property.transformer_factory"
 */
class TransformerFactory
{
    const CUSTOM_NAMESPACE = '\RentJeeves\ImportBundle\PropertyImport\Transformer\Custom\\';

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $defaultTransformers;

    /**
     * @var CsvTransformer
     */
    protected $csvTransformer;

    /**
     * @var array
     */
    protected $pathsToCustomTransformers;

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     * @param array                  $pathsToCustomTransformers
     * @param array                  $defaultTransformers
     * @param CsvTransformer         $csvTransformer
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        array $pathsToCustomTransformers,
        array $defaultTransformers,
        CsvTransformer $csvTransformer
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->pathsToCustomTransformers = $pathsToCustomTransformers;
        $this->defaultTransformers = $defaultTransformers;
        $this->csvTransformer = $csvTransformer;
    }

    /**
     * Get an import transformer for the given Group and external property
     *
     * There are default transformers for each accounting system type,
     * but these can be overridden for a given holding or group
     * or for a given external property id.
     * This allows us to add custom data transformation as needed.
     *
     * A transformer object will be returned based on the following increasing specificity:
     *
     *          AccountSystemDefault > Holding > Group > ExternalPropertyID
     *
     * @param Group  $group
     * @param string $externalPropertyId
     *
     * @throws ImportException if can`t return correct transformer
     *
     * @return TransformerInterface returns an import transformer object
     */
    public function getTransformer(Group $group, $externalPropertyId = null)
    {
        if (null === $importSettings = $group->getCurrentImportSettings()) {
            throw new ImportInvalidArgumentException(
                sprintf('Group#%d doesn`t have settings for import.', $group->getId())
            );
        }
        if (ImportSource::CSV === $importSettings->getSource()) {
            return $this->csvTransformer;
        } else {
            $customClassName = $this->em->getRepository('RjDataBundle:ImportTransformer')
                ->findClassNameWithPriorityByGroupAndExternalPropertyId($group, $externalPropertyId);

            $accountingSystemName = $group->getHolding()->getAccountingSystem();
            if (false === in_array($accountingSystemName, array_keys($this->defaultTransformers))) {
                throw new ImportInvalidArgumentException(
                    sprintf(
                        'TransformerFactory: Accounting System with name "%s" is not supported.',
                        $accountingSystemName
                    )
                );
            }

            if ($customClassName !== null) {
                return $this->getCustomTransformer($customClassName, $group);
            }

            return $this->defaultTransformers[$accountingSystemName];
        }
    }

    /**
     * @param string $className
     * @param Group  $group
     *
     * @throws ImportException if can`t find customTransformer file or
     * can`t create object for custom class or
     * custom transformer not implements TransformerInterface
     *
     * @return TransformerInterface Instance of custom class which overrides base transformer
     */
    protected function getCustomTransformer($className, Group $group)
    {
        $this->logger->debug(
            sprintf('Found className for custom Transformer : "%s".', $className),
            ['group' => $group->getId()]
        );

        $customTransformerClass = static::CUSTOM_NAMESPACE . $className;
        // if the class exists - there is no sense to register a new class. Just create new instance of this class
        if (false === class_exists($customTransformerClass)) {
            $this->registerUnregisteredCustomTransformer($className, $group);
        }

        $accountingSystemName = $group->getHolding()->getAccountingSystem();
        $baseTransformer = $this->defaultTransformers[$accountingSystemName];
        if (get_parent_class($customTransformerClass) !== get_class($baseTransformer)) {
            $this->logger->warning(
                $message = sprintf(
                    'Custom transformer for this Group must be override "%s".',
                    get_class($baseTransformer)
                ),
                ['group' => $group]
            );

            throw new ImportException($message);
        }

        return new $customTransformerClass($this->em, $this->logger);
    }

    /**
     * @param string $className
     * @param Group  $group
     *
     * @throws ImportException custom file not found or custom file incorrect
     */
    protected function registerUnregisteredCustomTransformer($className, Group $group)
    {
        if (true === empty($this->pathsToCustomTransformers)) {
            $this->logger->warning(
                $message = sprintf(
                    'Transformers not register if you do not specify paths to directories with custom transformers. ' .
                    'Pls add paths to directories with custom transformers to config-file.',
                    $className
                ),
                ['group' => $group]
            );

            throw new ImportException($message);
        }
        $finder = new Finder();
        $finder->files()->name($className . '.php');
        foreach ($this->pathsToCustomTransformers as $path) {
            $finder->in($path)->depth(0);
        }

        if ($finder->count() === 0) {
            $this->logger->warning(
                $message = sprintf(
                    'Not found any files with name "%s.php" in all directories for custom transformers.',
                    $className
                ),
                ['group' => $group]
            );

            throw new ImportException($message);
        }

        $file = current(iterator_to_array($finder->getIterator()));
        /** @var SplFileInfo $file */
        $customFilePath = $file->getRealpath();

        $this->logger->debug(
            sprintf(
                'Found file "%s" for className "%s".',
                $customFilePath,
                $className
            ),
            ['group' => $group]
        );

        include_once $customFilePath;

        $customTransformerClass = static::CUSTOM_NAMESPACE . $className;
        if (false === class_exists($customTransformerClass, false)) {
            $this->logger->warning(
                $message = sprintf(
                    'File is found, but it does not contain class "%s".' .
                    ' Pls check name and namespace in custom file "%s".',
                    $customTransformerClass,
                    $customFilePath
                ),
                ['group' => $group]
            );

            throw new ImportException($message);
        }
    }
}
