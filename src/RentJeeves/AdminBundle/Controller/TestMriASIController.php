<?php
namespace RentJeeves\AdminBundle\Controller;

use RentJeeves\AdminBundle\Form\MriASIType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TestMriASIController extends Controller
{
    /**
     * @Route("/test/asi/mri", name="asi_mri_test")
     * @Template("AdminBundle:TestMriASI:index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        $form = $this->createForm(
            new MriASIType(),
            null,
            [
                'action' => $this->generateUrl('new_integration_user', ['accountingSystem' => 'mri'])
            ]
        );

        return ['form' => $form->createView()];
    }

    /**
     * @Route(
     *     "/test/asi/mri/generate_digest",
     *     name="asi_mri_test_generate_digest",
     *     options={"expose"=true}
     * )
     * @Method({"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateDigest(Request $request)
    {
        return new JsonResponse([
            'digest' => $this->get('mri.hmac.generator')->generateHMAC($request->request->all())
        ]);
    }
}
