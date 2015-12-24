<?php

namespace RentJeeves\CoreBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;
    /**
     * @var string
     */
    protected $class;

    /**
     * @param ObjectManager $objectManager
     * @param $class
     */
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($entity)
    {
        if (null === $entity || !is_object($entity) || !method_exists($entity, 'getId')) {
            return null;
        }

        return $entity->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }
        try {
            $entity = $this->objectManager
                ->getRepository($this->class)
                ->find($id);
            if (null === $entity) {
                throw new TransformationFailedException('Error on reverse transformation: Entity not found');
            }

            return $entity;
        } catch (MappingException $e) {
            throw new TransformationFailedException('Error on reverse transformation: ' . $e->getMessage());
        }
    }
}
