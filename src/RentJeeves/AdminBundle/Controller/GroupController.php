<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\CoreBundle\Controller\BaseController;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ImportModelType;
use RentJeeves\DataBundle\Enum\ImportStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class GroupController extends BaseController
{
    /**
     * @param Group   $group
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("import/property/createJob/{group_id}", name="admin_create_import_property_job")
     * @ParamConverter("group", class="DataBundle:Group", options={"id" = "group_id"})
     * @Method({"GET"})
     */
    public function importPropertyCreateJob(Group $group, Request $request)
    {
        $propertiesMapping = $this->getEntityManager()
            ->getRepository('RjDataBundle:PropertyMapping')->getPropertiesMappingByGroup($group);

        if (empty($propertiesMapping)) {
            $request->getSession()->getFlashBag()->add(
                'error',
                $this->getTranslator()->trans(
                    'admin.import_properties_job.error.empty_external_properties',
                    ['%group_name%' => $group->getName()]
                )
            );
        } else {
            $newImport = new Import();
            $newImport->setImportType(ImportModelType::PROPERTY);
            $newImport->setUser($this->getUser());
            $newImport->setGroup($group);
            $newImport->setStatus(ImportStatus::RUNNING);

            $this->getEntityManager()->persist($newImport);
            $this->getEntityManager()->flush();

            foreach ($propertiesMapping as $propertyMapping) {
                $this->createJobForExternalProperty($newImport, $propertyMapping['externalPropertyId']);
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                $this->getTranslator()->trans(
                    'admin.import_properties_job.created',
                    ['%group_name%' => $group->getName()]
                )
            );
        }

        return $this->redirectToRoute('admin_rj_group_list');
    }

    /**
     * @param Import $import
     * @param string $externalPropertyId
     */
    protected function createJobForExternalProperty(Import $import, $externalPropertyId)
    {
        $job = new Job(
            'renttrack:import:property',
            [
                '--import-id=' . $import->getId(),
                '--external-property-id=' . $externalPropertyId
            ]
        );

        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();
    }
}
