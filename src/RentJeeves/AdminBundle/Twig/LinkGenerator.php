<?php
namespace RentJeeves\AdminBundle\Twig;

use JMS\JobQueueBundle\Twig\LinkGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\JobRelatedPayment;
use RentJeeves\DataBundle\Entity\JobRelatedOrder;

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
       'RentJeeves\DataBundle\Entity\JobRelatedPayment' => 'Payment',
       'RentJeeves\DataBundle\Entity\JobRelatedOrder' => 'Order',
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

    public function supports($entity)
    {
        return (bool)isset($this->entities[get_class($entity)]);
    }

    public function generate($entity)
    {
        if ($entity instanceof JobRelatedPayment) {
            return $this->routeGenerator->generate(
                'admin_rentjeeves_data_payment_show',
                array(
                    'id' => $entity->getPayment()->getId()
                )
            );
        }
        if ($entity instanceof JobRelatedOrder) {
            return $this->routeGenerator->generate(
                'admin_creditjeeves_data_order_show',
                array(
                    'id' => $entity->getOrder()->getId()
                )
            );
        }
    }

    public function getLinkname($entity)
    {
        return $this->entities[get_class($entity)];
    }
}
