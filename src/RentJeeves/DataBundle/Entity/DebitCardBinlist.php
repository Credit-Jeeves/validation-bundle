<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\DebitCardBinlist as Base;

/**
 * @ORM\Table(name="rj_debit_card_binlist")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\DebitCardBinlistRepository")
 */
class DebitCardBinlist extends Base
{
}
