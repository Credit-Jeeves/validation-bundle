<?php
namespace CreditJeeves\CoreBundle\EventListener;

use Gedmo\Loggable\LoggableListener as Base;
use Doctrine\Common\EventArgs;
use Gedmo\Mapping\MappedEventSubscriber;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use CreditJeeves\DataBundle\Entity\Lead;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 */
class Loggable extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function createLogEntry($action, $object, LoggableAdapter $ea)
    {
        if (($object instanceof Lead) && self::ACTION_REMOVE === $action) {
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
            $uow = $om->getUnitOfWork();
            $logEntry->setObjectId($objectId);
            $newValues = array();
            if ($action !== self::ACTION_REMOVE && isset($config['versioned'])) {
                foreach ($ea->getObjectChangeSet($uow, $object) as $field => $changes) {
                    if (!in_array($field, $config['versioned'])) {
                        continue;
                    }
                    $value = $changes[1];
                    if ($meta->isSingleValuedAssociation($field) && $value) {
                        $oid = spl_object_hash($value);
                        $wrappedAssoc = AbstractWrapper::wrap($value, $om);
                        $value = $wrappedAssoc->getIdentifier(false);
                        if (!is_array($value) && !$value) {
                            $this->pendingRelatedObjects[$oid][] = array(
                                'log' => $logEntry,
                                'field' => $field
                            );
                        }
                    }
                    $newValues[$field] = $value;
                }
                $logEntry->setData($newValues);
            }
            
            if ($action === self::ACTION_UPDATE && 0 === count($newValues)) {
                return;
            }

            if (!($object instanceof Lead)) {
                $version = 1;
                if ($action !== self::ACTION_CREATE) {
                    $version = $ea->getNewVersion($logEntryMeta, $object);
                    if (empty($version)) {
                        // was versioned later
                        $version = 1;
                    }
                }
                $logEntry->setVersion($version);
            }

            $this->prePersistLogEntry($logEntry, $object);

            $om->persist($logEntry);
            $uow->computeChangeSet($logEntryMeta, $logEntry);
        }
    }
}
