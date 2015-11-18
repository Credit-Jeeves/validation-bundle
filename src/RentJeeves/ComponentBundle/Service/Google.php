<?php

namespace RentJeeves\ComponentBundle\Service;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\ComponentBundle\Service\Google\GooglePlaces as Place;

/**
 * Service name "google"
 *
 * @todo Rename this pls
 */
class Google
{
    const DEFAULT_NAME = 'property';
    const DEFAULT_TYPES = 'lodging';
    const DEFAULT_LANGUAGE = 'en';
    const DEFAULT_ACCURACY = 50;
    const DEFAULT_RADIUS = 1000;
    const DEFAULT_LIMIT = 19;

    /**
     * @var Place
     */
    protected $place;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     * @param string $google_maps_key
     */
    public function __construct(EntityManager $em, $google_maps_key)
    {
        $this->em = $em;
        $this->place = new Place($google_maps_key);
    }

    /**
     * Save property to google
     *
     * @param Property $property
     * @param string $name
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function savePlace(Property $property, $name = self::DEFAULT_NAME)
    {
        $groups = $property->getPropertyGroups();

        //Save Property to google only if it have landlord or not saved into google before
        if (empty($groups) || $property->getGoogleReference()) {
            return false;
        }

        //Call this for remove duplicates
        $this->clearPlace($property);

        $this->place->setLocation($this->getLocationData($property));
        $this->place->setLanguage(self::DEFAULT_LANGUAGE);
        $this->place->setAccuracy(self::DEFAULT_ACCURACY);
        $this->place->setName($name);
        $this->place->setTypes(self::DEFAULT_TYPES);
        $this->place->setSensor('false');

        $result = $this->place->add();

        if (is_object($result) && property_exists($result, 'reference')) {
            $property->setGoogleReference($result->reference);
            $this->em->persist($property);
            $this->em->flush();

            return true;
        }

        throw new \Exception(
            sprintf(
                "Error processing(save) google request for Property ID = %s, Error: %s",
                $property->getId(),
                print_r($result, true)
            )
        );
    }

    /**
     * @param Property $property
     * @param string $reference
     * @param string $name
     *
     * @return bool
     */
    public function deletePlace(Property $property, $reference, $name = self::DEFAULT_NAME)
    {
        $this->place->setLocation($this->getLocationData($property));
        $this->place->setLanguage(self::DEFAULT_LANGUAGE);
        $this->place->setAccuracy(self::DEFAULT_ACCURACY);
        $this->place->setName($name);
        $this->place->setTypes(self::DEFAULT_TYPES);
        $this->place->setSensor('false');
        $this->place->setReference($reference);
        $results = $this->place->delete();

        if (!is_object($results) || !property_exists($results, 'status') || $results->status != Place::OK_STATUS) {
            return false;
        }

        return true;
    }

    /**
     * @param Property $property
     * @param string $name
     * @param int $radius
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function searchPlace(Property $property, $name = self::DEFAULT_NAME, $radius = self::DEFAULT_RADIUS)
    {
        $this->place->setLocation($this->getLocationData($property));
        $this->place->setRadius($radius);
        $this->place->setLanguage(self::DEFAULT_LANGUAGE);
        $this->place->setAccuracy(self::DEFAULT_ACCURACY);
        $this->place->setName($name);
        $this->place->setTypes(self::DEFAULT_TYPES);
        $this->place->setSensor('false');

        $results = $this->place->Search();

        if (empty($results['errors']) && isset($results['result'])) {
            return $results['result'];
        }

        throw new \Exception('Error processing(searchPlace) google request for Property ID#' . $property->getId(), 1);
    }

    /**
     * @param Property $property
     */
    protected function clearPlace(Property $property)
    {
        $searchResult = $this->searchPlace($property, self::DEFAULT_NAME, 50);

        if (empty($searchResult)) {
            return;
        }

        $locationData = $this->getLocationData($property, false);

        foreach ($searchResult as $value) {
            $lat = $value['geometry']['location']['lat'];
            $lng = $value['geometry']['location']['lng'];
            if ($lat == $locationData['latitude'] && $lng == $locationData['longitude']) {
                $this->deletePlace($property, $value['reference']);
            }
        }
    }

    /**
     * @param Property $property
     * @param boolean $toString
     *
     * @return array
     *
     * @throws \InvalidArgumentException when Property doesn\'t have location data
     */
    protected function getLocationData(Property $property, $toString = true)
    {
        $propertyAddress = $property->getPropertyAddress();
        if ($propertyAddress->getJb() && $propertyAddress->getKb()) {
            $locationData['latitude'] = $propertyAddress->getJb();
            $locationData['longitude'] = $propertyAddress->getKb();
        } elseif ($propertyAddress->getLat() && $propertyAddress->getLong()) {
            $locationData['latitude'] = $propertyAddress->getLat();
            $locationData['longitude'] = $propertyAddress->getLong();
        } else {
            throw new \InvalidArgumentException(sprintf('Property doesn\'t have neither jb/kb nor lat/long'));
        }

        return $toString ? $locationData['latitude'] . ',' . $locationData['longitude'] : $locationData;
    }
}
