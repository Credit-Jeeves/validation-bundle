<?php

namespace RentJeeves\TestBundle\FixtureGenerator\Services;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\Alice\Fixtures\Loader;
use Nelmio\Alice\Persister\Doctrine as AlicePersisterDoctrine;

class FixtureLoadManager
{
    /** @var Loader */
    protected $loader;

    /** @var AlicePersisterDoctrine */
    protected $persister;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->loader = new Loader();
        $this->persister = new AlicePersisterDoctrine($em);
    }

    /**
     * @param array $fixtures
     * @param callable $callback
     * @return array
     */
    public function load(array $fixtures, $callback)
    {
        $references = [];
        $objects = [];

        foreach ($fixtures as $filename) {
            $callback(sprintf('Loading file "%s".', $filename));

            $this->loader->setReferences($references);
            $newObjects = $this->loader->load($filename);
            $references = $this->loader->getReferences();

            $callback(sprintf('Loaded %s entities . Done!', count($newObjects)));

            $objects = array_merge($objects, $newObjects);
        }
        if (count($objects) > 0) {
            $this->persist($objects, $callback);
        }

        return $objects;
    }

    /**
     * @param array $entities
     * @param callable $callback
     */
    protected function persist(array $entities, $callback)
    {
        $callback(sprintf('Persisted %s entities', count($entities)));
        $this->persister->persist($entities);
    }
}
