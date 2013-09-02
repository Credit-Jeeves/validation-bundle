<?php
namespace RentJeeves\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CoreBundle\Validator\InviteEmail;

class ValidateController extends Controller
{
    /**
     * @Route("/validator/inviteEmail", name="rj.core.validator.inviteEmail")
     * @Template()
     *
     * @return json Response
     */
    public function inviteEmailAction()
    {
        $value = $this->get('request')->request->get('value');
        $inviteEmail = new InviteEmail();
        $validator = $this->get($inviteEmail->validatedBy());
        $data = array();
        if ($validator->validate($value, $inviteEmail)) {
            $data['error'] = $error;
            $data['status'] = 'error';
        } else {
            $data['status'] = 'ok';
        }
        return new Response(json_encode($data));
    }
}
