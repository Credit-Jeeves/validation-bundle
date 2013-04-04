<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Utility\VehicleUtility as Vehicle;

class GoodsController extends Controller
{
    /**
     *
     * @param \CreditJeeves\DataBundle\Entity\Lead $Lead
     */
    public function indexAction(\CreditJeeves\DataBundle\Entity\Lead $Lead)
    {
        $Group = $Lead->getGroup();
        $sType = $Group->getType();
        switch ($sType) {
            case Group::TYPE_VEHICLE:
                $Make = null;
                $Model = null;
                $Vehicle = $this->getUser()->getVehicle();
                if (!empty($Vehicle)) {
                    $Make = $this->getUser()->getVehicle()->getMake();
                    $Model = $this->getUser()->getVehicle()->getModel();

                }
                $sImageUrl = Vehicle::getAmazonVehicle($Make, $Model, $this->container);
                $sLink = $Group->getWebsiteUrl();

                return $this->render(
                    'ComponentBundle:Goods:vehicle.html.twig',
                    array(
                        'imageUrl' => $sImageUrl,
                        'Make' => $Make,
                        'Model' => $Model,
                        'sLink' => $sLink,
                    )
                );
                break;
            case Group::TYPE_ESTATE:
                return $this->render('ComponentBundle:Goods:estate.html.twig', array());
                break;
            default:
                return $this->render('ComponentBundle:Goods:index.html.twig', array());
        }

    }
}
