<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Model\ImportSummary as Base;

/**
 * @ORM\Table(name="rj_import_summary")
 * @ORM\Entity()
 */
class ImportSummary extends Base
{

    /**
     * @return int
     */
    public function countExceptions()
    {
        if ($this->errors->count() == 0) {
            return 0;
        }

        $errors = $this->errors->filter(
            function (ImportError $importError) {
                $exceptionUid = $importError->getExceptionUid();

                return !empty($exceptionUid);
            }
        );

        return $errors->count();
    }

    /**
     * @return int
     */
    public function countErrors()
    {
        if ($this->errors->count() == 0) {
            return 0;
        }

        $errors = $this->errors->filter(
            function (ImportError $importError) {
                $exceptionUid = $importError->getExceptionUid();

                return empty($exceptionUid);
            }
        );

        return $errors->count();
    }
}
