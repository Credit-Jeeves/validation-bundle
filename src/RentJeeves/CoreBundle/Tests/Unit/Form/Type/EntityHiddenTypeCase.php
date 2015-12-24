<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\Mapping\MappingException;
use RentJeeves\CoreBundle\Form\Type\EntityHiddenType;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class EntityHiddenTypeCase extends TypeTestCase
{
    use CreateSystemMocksExtensionTrait;

    const VALID_ENTITY_ID = 1;

    const VALID_ENTITY_CLASS = 'CreditJeeves\DataBundle\Entity\Group';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @test
     */
    public function submitValidData()
    {
        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->getEntityRepositoryMock(self::VALID_ENTITY_CLASS));
        $data = [
            'entity' => self::VALID_ENTITY_ID,
        ];
        $type = new EntityHiddenTypeTestFom();
        $form = $this->factory->create($type, [], ['class' => self::VALID_ENTITY_CLASS]);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized(), 'Form should synchronize.');

        $entity = $this->getEntityMock(self::VALID_ENTITY_CLASS, self::VALID_ENTITY_ID);

        $this->assertEquals($entity, $form->get('entity')->getData(), 'Entities should be equal after transformation');

        $view = $form->createView();

        $viewEntity = $view->offsetGet('entity');

        $this->assertEquals($data['entity'], $viewEntity->vars['value'], 'View should have submitted value');
    }

    /**
     * @test
     */
    public function submitInValidData()
    {
        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->getEntityRepositoryMock(self::VALID_ENTITY_CLASS));
        $data = [
            'entity' => self::VALID_ENTITY_ID + 1,
        ];
        $type = new EntityHiddenTypeTestFom();
        $form = $this->factory->create($type, [], ['class' => self::VALID_ENTITY_CLASS]);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized(), 'Form should synchronize.');

        $this->assertNull($form->get('entity')->getData(), 'Entity should be null after transformation(id is invalid)');

        $view = $form->createView();

        $viewEntity = $view->offsetGet('entity');

        $this->assertEquals($data['entity'], $viewEntity->vars['value'], 'View should have submitted value');
    }

    /**
     * @test
     */
    public function createFormWithInvalidClass()
    {
        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->willThrowException(MappingException::nonExistingClass('InvalidClass'));
        $data = [
            'entity' => self::VALID_ENTITY_ID,
        ];
        $type = new EntityHiddenTypeTestFom();
        $form = $this->factory->create($type, [], ['class' => 'InvalidClass']);
        $form->submit($data);

        $this->assertTrue($form->isSynchronized(), 'Form should synchronize.');

        $this->assertNull(
            $form->get('entity')->getData(),
            'Entity should be null after transformation(class is invalid)'
        );

        $view = $form->createView();

        $viewEntity = $view->offsetGet('entity');

        $this->assertEquals($data['entity'], $viewEntity->vars['value'], 'View should have submitted value');
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $this->em = $this->getEntityManagerMock();
        $entityHidden = new EntityHiddenType($this->em);

        return [
            new PreloadedExtension([$entityHidden->getName() => $entityHidden], []),
        ];
    }

    /**
     * @param $entityClass
     * @param null $id
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getEntityMock($entityClass, $id = null)
    {
        $entity = $this->getMock($entityClass, [], [], '', false);
        $entity->expects($this->any())->method('getId')->willReturn($id);

        return $entity;
    }

    /**
     * @param $entityClass
     * @return \Doctrine\ORM\EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getEntityRepositoryMock($entityClass)
    {
        $repo = $this->getMock('Doctrine\ORM\EntityRepository', [], [], '', false);
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
