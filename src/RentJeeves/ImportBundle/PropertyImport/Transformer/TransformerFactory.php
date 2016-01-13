<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ImportTransformerRepository;
use RentJeeves\ImportBundle\Exception\ImportException;
use RentJeeves\ImportBundle\Exception\ImportInvalidArgumentException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class TransformerFactory
{
    const CUSTOM_NAMESPACE = '\RentJeeves\ImportBundle\PropertyImport\Transformer\Custom\\';

    /**
     * @var ImportTransformerRepository
     */
    protected $importTransformerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $defaultTransformers;

    /**
     * @var array
     */
    protected $pathsToCustomTransformers;

    /**
     * @param ImportTransformerRepository $importTransformerRepository
     * @param LoggerInterface             $logger
     * @param array                       $pathsToCustomTransformers
     * @param array                       $defaultTransformers
     */
    public function __construct(
        ImportTransformerRepository $importTransformerRepository,
        LoggerInterface $logger,
        array $pathsToCustomTransformers,
        array $defaultTransformers
    ) {
        $this->importTransformerRepository = $importTransformerRepository;
        $this->logger = $logger;
        $this->pathsToCustomTransformers = $pathsToCustomTransformers;
        $this->defaultTransformers = $defaultTransformers;
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
    public function getTransformer(Group $group, $externalPropertyId)
    {
        $customClassName = $this->importTransformerRepository->findClassNameWithPriorityByGroupAndExternalPropertyId(
            $group,
            $externalPropertyId
        );

        if ($customClassName !== null) {
            return $this->getCustomTransformer($customClassName, $group);
        }

        $accountingSystemName = $group->getHolding()->getApiIntegrationType();
        if (false === in_array($accountingSystemName, array_keys($this->defaultTransformers))) {
            throw new ImportInvalidArgumentException(
                sprintf('Accounting System with name "%s" is not supported.', $accountingSystemName)
            );
        }

        return $this->defaultTransformers[$accountingSystemName];
    }

    /**
     * @param string $className
     * @param Group  $group
     *
     * @throws ImportException if can`t find customTransformer file or
     * can`t create object for custom class or
     * custom transformer not implements TransformerInterface
     *
     * @return TransformerInterface Instance of custom class
     */
    protected function getCustomTransformer($className, Group $group)
    {
        $this->logger->debug(
            sprintf('Found className for custom Transformer : "%s".', $className),
            ['group_id' => $group->getId()]
        );

        if (true === class_exists(static::CUSTOM_NAMESPACE . $className, false)) {
            // if the class exists - there is no sense to register a new class. Just create new instance of this class
            $customTransformerClass = static::CUSTOM_NAMESPACE . $className;
            $customTransformer = new $customTransformerClass();
        } else {
            $customTransformer = $this->createUnregisteredCustomTransformer($className, $group);
        }

        if (!$customTransformer instanceof TransformerInterface) {
            $this->logger->warning(
                $message = 'Custom transformer must be instance of "TransformerInterface".',
                ['group_id' => $group->getId()]
            );

            throw new ImportException($message);
        }

        return $customTransformer;
    }

    /**
     * @param string $className
     * @param Group  $group
     *
     * @throws ImportException custom file not found or custom file incorrect
     *
     * @return object
     */
    protected function createUnregisteredCustomTransformer($className, Group $group)
    {
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
                ['group_id' => $group->getId()]
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
            ['group_id' => $group->getId()]
        );

        include_once $customFilePath;

        $customTransformerClass = static::CUSTOM_NAMESPACE . $className;
        if (false === class_exists($customTransformerClass, false)) {
            $this->logger->warning(
                $message = sprintf(
                    'File is found, but can`t create instance of class "%s".' .
                    ' Pls check name and namespace in custom file "%s".',
                    $customTransformerClass,
                    $customFilePath
                ),
                ['group_id' => $group->getId()]
            );

            throw new ImportException($message);
        }

        return new $customTransformerClass();
    }
}
