<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Enum\ImportSource;
use RentJeeves\DataBundle\Model\ImportGroupSettings as Base;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *      name="rj_group_import_settings"
 * )
 * @ORM\Entity()
 */
class ImportGroupSettings extends Base
{
    /**
     * @Assert\True(
     *     message="admin.errors.api_property_ids.empty",
     *     groups={"import_settings"}
     * )
     */
    public function isNotBlankApiPropertyIdsForIntegratedApiSource()
    {
        if ($this->source === ImportSource::INTEGRATED_API) {
            return !empty($this->apiPropertyIds);
        }

        return true;
    }

    /**
     * @Assert\True(
     *     message="admin.errors.csv_settings.empty",
     *     groups={"import_settings"}
     * )
     */
    public function isNotBlankCsvSettingFieldsForCsvSource()
    {
        if ($this->source === ImportSource::CSV) {
            return (!empty($this->csvDateFormat) && !empty($this->csvFieldDelimiter) &&
                !empty($this->csvTextDelimiter));
        }

        return true;
    }
}
