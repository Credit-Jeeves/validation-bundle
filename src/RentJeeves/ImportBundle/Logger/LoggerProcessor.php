<?php

namespace RentJeeves\ImportBundle\Logger;

use CreditJeeves\DataBundle\Entity\Group;

class LoggerProcessor
{
    const ERROR_VALUE = 'ERROR';

    /**
     * Add new vars for current record
     * using for "monolog.formatter.import"
     *
     * @param array $record
     *
     * @return array
     */
    public function processRecord(array $record)
    {
        $group = isset($record['context']['group']) ? $record['context']['group'] : null;
        $record['extra']['additional_parameter'] = isset($record['context']['additional_parameter']) ?
            $record['context']['additional_parameter'] : self::ERROR_VALUE;

        if ($group instanceof Group) {
            $record['extra']['group_id'] = $group->getId();
            $record['extra']['import_type'] = $group->getImportSettings() ?
                $group->getImportSettings()->getSource() : self::ERROR_VALUE;

        } else {
            $record['extra']['group_id'] = self::ERROR_VALUE;
            $record['extra']['import_type'] = self::ERROR_VALUE;
        }

        return $record;
    }
}
