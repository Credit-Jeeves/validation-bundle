<?php
namespace RentJeeves\DataBundle\Tests\Entity;

use RentJeeves\DataBundle\Entity\ImportTransformer;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class ImportTransformerRepositoryCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldReturnClassNameForExternalPropertyId()
    {
        $this->load(true);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);

        $newImportTransformer1 = new ImportTransformer();
        $newImportTransformer1->setHolding($group->getHolding());
        $newImportTransformer1->setExternalPropertyId('test');
        $newImportTransformer1->setClassName('Class1');

        $newImportTransformer2 = new ImportTransformer();
        $newImportTransformer2->setGroup($group);
        $newImportTransformer2->setClassName('Class2');

        $newImportTransformer3 = new ImportTransformer();
        $newImportTransformer3->setHolding($group->getHolding());
        $newImportTransformer3->setClassName('Class3');

        $this->getEntityManager()->persist($newImportTransformer1);
        $this->getEntityManager()->persist($newImportTransformer2);
        $this->getEntityManager()->persist($newImportTransformer3);

        $this->getEntityManager()->flush();

        $result = $this->getEntityManager()->getRepository('RjDataBundle:ImportTransformer')
            ->findClassNameWithPriorityByGroupAndExternalPropertyId($group, 'test');

        $this->assertEquals('Class1', $result, 'Query return incorrect result');
    }

    /**
     * @test
     */
    public function shouldReturnClassNameForGroup()
    {
        $this->load(true);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);

        $newImportTransformer2 = new ImportTransformer();
        $newImportTransformer2->setGroup($group);
        $newImportTransformer2->setClassName('Class2');

        $newImportTransformer3 = new ImportTransformer();
        $newImportTransformer3->setHolding($group->getHolding());
        $newImportTransformer3->setClassName('Class3');

        $this->getEntityManager()->persist($newImportTransformer2);
        $this->getEntityManager()->persist($newImportTransformer3);

        $this->getEntityManager()->flush();

        $result = $this->getEntityManager()->getRepository('RjDataBundle:ImportTransformer')
            ->findClassNameWithPriorityByGroupAndExternalPropertyId($group, 'test');

        $this->assertEquals('Class2', $result, 'Query return incorrect result');
    }

    /**
     * @test
     */
    public function shouldReturnClassNameForHolding()
    {
        $this->load(true);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);

        $newImportTransformer3 = new ImportTransformer();
        $newImportTransformer3->setHolding($group->getHolding());
        $newImportTransformer3->setClassName('Class3');

        $this->getEntityManager()->persist($newImportTransformer3);
        $this->getEntityManager()->flush();

        $result = $this->getEntityManager()->getRepository('RjDataBundle:ImportTransformer')
            ->findClassNameWithPriorityByGroupAndExternalPropertyId($group, 'test');

        $this->assertEquals('Class3', $result, 'Query return incorrect result');
    }

    /**
     * @test
     */
    public function shouldReturnNullIfDbDoesNotHaveRowsForInputParameters()
    {
        $this->load(true);

        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->find(24);

        $result = $this->getEntityManager()->getRepository('RjDataBundle:ImportTransformer')
            ->findClassNameWithPriorityByGroupAndExternalPropertyId($group, 'test');

        $this->assertNull($result, 'Query return incorrect result');
    }
}
