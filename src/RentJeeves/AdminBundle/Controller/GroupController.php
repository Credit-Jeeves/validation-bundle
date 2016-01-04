<?php

namespace RentJeeves\AdminBundle\Controller;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Job;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Translation\Translator;

/**
 * @Route("/")
 */
class GroupController extends Controller
{
    /**
     * @Route("import/property/createJob/{group_id}", name="admin_create_import_property_job")
     * @ParamConverter("group", class="DataBundle:Group", options={"id" = "group_id"})
     * @Method({"GET"})
     */
    public function importPropertyCreateJob(Group $group, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine')->getManager();
        /** @var Translator $translator */
        $translator = $this->get('translator');
        $propertiesMapping = $em->getRepository('RjDataBundle:PropertyMapping')->getPropertiesMappingByGroup($group);
        $urlLink = $request->server->get('HTTP_REFERER');

        if (empty($urlLink)) {
            $urlLink = $this->get('router')->generate('admin_rj_group_list', [], true);
        }

        if (empty($propertiesMapping)) {
            $request->getSession()->getFlashBag()->add(
                'error',
                $translator->trans(
                    'admin.import_properties_job.error.empty_external_properties',
                    ['%group_name%' => $group->getName()]
                )
            );

            return new RedirectResponse($urlLink);
        }

        foreach ($propertiesMapping as $propertyMapping) {
            $job = new Job(
                'renttrack:import:property',
                [
                    '--app=rj',
                    sprintf('--group-id=%s', $group->getId()),
                    sprintf('--external-property-id=%s', $propertyMapping['externalPropertyId'])
                ]
            );
            $em->persist($job);
        }
        
        $em->flush();

        $request->getSession()->getFlashBag()->add(
            'success',
            $translator->trans('admin.import_properties_job.created', ['%group_name%' => $group->getName()])
        );


        return new RedirectResponse($urlLink);
    }
}
