<?php

namespace RentJeeves\ComponentBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\ComponentBundle\Service\Google\GooglePlaces as Place;
use Doctrine\ORM\EntityManager;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @DI\Service("google")
 */
class Google
{
    const DEFAULT_NAME = 'property';

    const DEFAULT_TYPES = 'lodging';

    const DEFAULT_LANGUAGE = 'en';

    const DEFAULT_ACCURANCY = 50;

    const DEFAULT_RADIUS = 1000;

    const DEFAULT_LIMIT = 19;

    /**
     * @var GooglePlaces
     */
    protected $place;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param parameter from config     $google_maps_key
     *
     * @DI\InjectParams({
     *     "google_maps_key"    = @DI\Inject("%google_maps_key%"),
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     * })
     *
     * @access public
     */
    public function __construct($google_maps_key, EntityManager $em)
    {
        $this->place = new Place($google_maps_key);
        $this->em = $em;
    }

    /**
    * Save property to google
    */
    public function savePlace(Property $property, $name = self::DEFAULT_NAME)
    {
        $groups = $property->getPropertyGroups();

        //Save Property to google only if it have landlord or not saved into google before
        if (empty($groups) || $property->getGoogleReference()) {
            return false;
        }

        //Call this for remove dublicates
        $this->clearPlace($property);

        $latitude   = $property->getJb();
        $longitude = $property->getKb();
        $this->place->setLocation($latitude . ',' . $longitude);
        $this->place->setLanguage(self::DEFAULT_LANGUAGE);
        $this->place->setAccuracy(self::DEFAULT_ACCURANCY);
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

    public function deletePlace(Property $property, $reference, $name = self::DEFAULT_NAME)
    {
        $latitude   = $property->getJb();
        $longitude = $property->getKb();
        $this->place->setLocation($latitude . ',' . $longitude);
        $this->place->setLanguage(self::DEFAULT_LANGUAGE);
        $this->place->setAccuracy(self::DEFAULT_ACCURANCY);
        $this->place->setName($name);
        $this->place->setTypes(self::DEFAULT_TYPES);
        $this->place->setSensor('false');
        $this->place->setReference($reference);
        $results = $this->place->delete();

        if (!is_object($results) || !property_exists($results, 'status')) {
            return false;
        }

        if ($results->status != Place::OK_STATUS) {
            return false;
        }

        return true;
    }

    /**
    * Save property to google
    *
    * @return array
    */
    public function searchPlace(Property $property, $name = self::DEFAULT_NAME, $radius = self::DEFAULT_RADIUS)
    {
        $latitude   = $property->getJb();
        $longitude = $property->getKb();
        $this->place->setLocation($latitude . ',' . $longitude);
        $this->place->setRadius($radius);
        $this->place->setLanguage(self::DEFAULT_LANGUAGE);
        $this->place->setAccuracy(self::DEFAULT_ACCURANCY);
        $this->place->setName($name);
        $this->place->setTypes(self::DEFAULT_TYPES);
        $this->place->setSensor('false');

        $results = $this->place->Search();

        if (empty($results['errors']) && isset($results['result'])) {
            return $results['result'];
        }

        throw new \Exception("Error processing(searchPlace) google request for Property ID =".$property->getId(), 1);
    }

    protected function clearPlace(Property $property)
    {

        $searchResult = $this->searchPlace($property, self::DEFAULT_NAME, 50);

        if (empty($searchResult)) {
            return true;
        }

        foreach ($searchResult as $value) {
            $lat = $value['geometry']['location']['lat'];
            $lng = $value['geometry']['location']['lng'];
            if ($lat == $property->getJb() && $property->getKb() == $lng) {
                $this->deletePlace($property, $value['reference']);
            }
        }
    }
}
