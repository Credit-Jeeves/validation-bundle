<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Form\DataTransformer;

use Doctrine\Common\Persistence\Mapping\MappingException;
use RentJeeves\CoreBundle\Form\DataTransformer\EntityToIdTransformer;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class EntityToIdTransformerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    const VALID_ENTITY_ID = 1;

    const VALID_ENTITY_CLASS = 'CreditJeeves\DataBundle\Entity\Group';

    /**
     * @test
     */
    public function shouldTransformFromValidEntity()
    {
        $transformer = new EntityToIdTransformer($this->getEntityManagerMock(), self::VALID_ENTITY_CLASS);

        $id = $transformer->transform(
            $this->getEntityMock(self::VALID_ENTITY_CLASS, self::VALID_ENTITY_ID)
        );

        $this->assertEquals($id, self::VALID_ENTITY_ID, 'Should be transform from entity object to entity id');
    }

    /**
     * @test
     */
    public function shouldReverseTransformToValidEntity()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->getEntityRepositoryMock(self::VALID_ENTITY_CLASS));
        $entity = $this->getEntityMock(self::VALID_ENTITY_CLASS, self::VALID_ENTITY_ID);

        $transformer = new EntityToIdTransformer($em, self::VALID_ENTITY_CLASS);

        $result = $transformer->reverseTransform(self::VALID_ENTITY_ID);

        $this->assertEquals($entity, $result, 'Should reverse transformation from id to entity');
    }

    /**
     * @test
     */
    public function shouldNotTransformFromInvalidEntity()
    {
        $object = new \stdClass();

        $transformer = new EntityToIdTransformer($this->getEntityManagerMock(), self::VALID_ENTITY_CLASS);

        $id = $transformer->transform($object);

        $this->assertNull($id, 'Should not be transform from invalid entity object to entity id (just get null)');
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function shouldNotReverseTransformToInvalidEntityClass()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->willThrowException(MappingException::nonExistingClass('InvalidClass'));

        $transformer = new EntityToIdTransformer($em, 'InvalidClass');

        $result = $transformer->reverseTransform(self::VALID_ENTITY_ID + 1);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function shouldNotReverseTransformFromInvalidEntityId()
    {
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->getEntityRepositoryMock(self::VALID_ENTITY_CLASS));

        $transformer = new EntityToIdTransformer($em, self::VALID_ENTITY_CLASS);

        $result = $transformer->reverseTransform(self::VALID_ENTITY_ID + 1);
    }

    /**
     * @param $entityClass
     * @param null $id
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEntityMock($entityClass, $id = null)
    {
        $entity = $this->getBaseMock($entityClass);
        $entity->expects($this->any())->method('getId')->willReturn($id);

        return $entity;
    }

    /**
     * @param $entityClass
     * @return \Doctrine\ORM\EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getEntityRepositoryMock($entityClass)
    {
        $repo = $this->getBaseMock('Doctrine\ORM\EntityRepository');
        $entity = $this->getEntityMock($entityClass, self::VALID_ENTITY_ID);
        $repo
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->returnCallback(function ($id) use ($entity) {
                    if ($id == self::VALID_ENTITY_ID) {
                        return $entity;
                    }

                    return null;
                })
            );

        return $repo;
    }
}
