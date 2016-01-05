<?php

namespace RentJeeves\ImportBundle\Logger;

class LoggerProcessor
{
    /**
     * Add new var for current record
     * using for "monolog.formatter.import"
     *
     * @param array $record
     *
     * @return array
     */
    public function processRecord(array $record)
    {
        $record['extra']['group_id'] = isset($record['context']['group_id']) ? $record['context']['group_id'] : 'Error';

        return $record;
    }
}
