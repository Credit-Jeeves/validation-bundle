<?php
namespace RentJeeves\DataBundle\EventListener;

use RentJeeves\DataBundle\Entity\Contract;
use Gedmo\Loggable\LoggableListener as Base;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use RentJeeves\DataBundle\Entity\Payment;

class LoggableListener extends Base
{
    protected function isLoggable($object)
    {
        if ($object instanceof Contract) {
            return true;
        }

        if ($object instanceof Payment) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function createLogEntry($action, $object, LoggableAdapter $ea)
    {
        if (!$this->isLoggable($object)) {
            return parent::createLogEntry($action, $object, $ea);
        }

        if (self::ACTION_REMOVE === $action) {
            return;
        }

        $om = $ea->getObjectManager();
        $wrapped = AbstractWrapper::wrap($object, $om);
        $meta = $wrapped->getMetadata();
        if ($config = $this->getConfiguration($om, $meta->name)) {
            $logEntryClass = $this->getLogEntryClass($ea, $meta->name);
            $logEntryMeta = $om->getClassMetadata($logEntryClass);
            /** @var \Gedmo\Loggable\Entity\LogEntry $logEntry */
            $logEntry = $logEntryMeta->newInstance();

            $logEntry->setAction($action);
            $logEntry->setUsername($this->username);
            $logEntry->setObjectClass($meta->name);
            $logEntry->setLoggedAt();

            // check for the availability of the primary key
            $objectId = $wrapped->getIdentifier();
            if (!$objectId && $action === self::ACTION_CREATE) {
                $this->pendingLogEntryInserts[spl_object_hash($object)] = $logEntry;
            }
            /** @var UnitOfWork $uow */
            $uow = $om->getUnitOfWork();
            $logEntry->setObject($object);
            $currentValues = [];
            $newValues = [];
            if ($action !== self::ACTION_REMOVE && isset($config['versioned'])) {
                foreach ($uow->getOriginalEntityData($object) as $field => $newValue) {
                    if (!in_array($field, $config['versioned'])) {
                        continue;
                    }
                    if ($meta->isSingleValuedAssociation($field) && $newValue) {
                        $oid = spl_object_hash($newValue);
                        $wrappedAssoc = AbstractWrapper::wrap($newValue, $om);
                        $newValue = $wrappedAssoc->getIdentifier(false);
                        if (!is_array($newValue) && !$newValue) {
                            $this->pendingRelatedObjects[$oid][] = array(
                                'log' => $logEntry,
                                'field' => $field
                            );
                        }
                    }
                    $currentValues[$field] = $newValue;
                }
                foreach ($uow->getEntityChangeSet($object) as $field => $changes) {
                    if (!in_array($field, $config['versioned'])) {
                        continue;
                    }
                    $newValue = $changes[1];
                    $oldValue = $changes[0];
                    if ($meta->isSingleValuedAssociation($field) && $newValue) {
                        $oid = spl_object_hash($newValue);
                        $wrappedAssoc = AbstractWrapper::wrap($newValue, $om);
                        $newValue = $wrappedAssoc->getIdentifier(false);
                        if (!is_array($newValue) && !$newValue) {
                            $this->pendingRelatedObjects[$oid][] = array(
                                'log' => $logEntry,
                                'field' => $field
                            );
                        }
                    }
                    //Timezone bug, date the same bug timezone different
                    if ($newValue instanceof \DateTime && $oldValue instanceof \DateTime) {
                        if ($newValue->getTimestamp() === $oldValue->getTimestamp()) {
                            continue;
                        }
                    }
                    // "100" and 100.00 the same value, we should not update
                    if (is_numeric($newValue)) {
                        $newValue = floatval($newValue);
                        $oldValue = floatval($oldValue);

                        if ($newValue === $oldValue) {
                            continue;
                        }
                    }

                    $newValues[$field] = $newValue;
                }
                $logEntry->setData($currentValues);
            }
            if ($action === self::ACTION_UPDATE && 0 === count($newValues)) {
                return;
            }

            $this->prePersistLogEntry($logEntry, $object);

            $om->persist($logEntry);
            $uow->computeChangeSet($logEntryMeta, $logEntry);
        }
    }
}
