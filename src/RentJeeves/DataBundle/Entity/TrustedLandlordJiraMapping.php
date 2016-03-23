<?php

namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\TrustedLandlordJiraMapping as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_trusted_landlord_jira_mapping")
 */
class TrustedLandlordJiraMapping extends Base
{

}
