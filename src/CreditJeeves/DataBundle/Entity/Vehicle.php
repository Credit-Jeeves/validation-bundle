<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Model\Vehicle as BaseVehicle;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\VehicleRepository")
 * @ORM\Table(name="cj_vehicle")
 */
class Vehicle extends BaseVehicle
{
}