<?php
namespace RentJeeves\AdminBundle\Twig;

use JMS\JobQueueBundle\Twig\LinkGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @DI\Service("admin.link_generator")
 * @DI\Tag("jms_job_queue.link_generator")
 */
class LinkGenerator implements LinkGeneratorInterface
{
    protected $routeGenerator;

    protected $entities = array(
       'RentJeeves\DataBundle\Entity\JobRelatedPayment'
    );

    /**
     * @DI\InjectParams({
     *     "routeGenerator"  = @DI\Inject("sonata.admin.route.default_generator")
     * })
     */
    public function __construct($routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    function supports($entity)
    {
        return in_array(get_class($entity), $this->entities);
    }

    function generate($entity)
    {
        return $this->routeGenerator->generate(
            'admin_rentjeeves_data_payment_show',
            array(
                'id' => $entity->getPayment()->getId()
            )
        );
    }

    function getLinkname($entity)
    {
        $namespace = get_class($entity);
        return substr($namespace, strrpos($namespace, '\\') + 1);
    }
} 
