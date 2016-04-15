<?php

namespace RentJeeves\TrustedLandlordBundle\Services;

use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;

interface TrustedLandlordServiceInterface
{

    /**
     * Search for existing mailing address and return its TrustedLandlord if found.
     *
     * @param TrustedLandlordDTO $trustedLandlordDTO
     *
     * @return TrustedLandlord|null
     */
    public function lookup(TrustedLandlordDTO $trustedLandlordDTO);

    /**
     * Create a new mailing address and new TrustedLandlord.
     *
     * @param TrustedLandlordDTO $trustedLandlordDTO
     *
     * @throw TrustedLandlordServiceException anything unexpected occurred.
     */
    public function create(TrustedLandlordDTO $trustedLandlordDTO);

    /**
     * Set new status for TrustedLandlord.
     *
     * @param TrustedLandlord         $trustedLandlord
     * @param string                  $status
     * @param TrustedLandlordDTO|null $trustedLandlordDTO
     */
    public function update(TrustedLandlord $trustedLandlord, $status, TrustedLandlordDTO $trustedLandlordDTO = null);
}
