<?php
namespace RentJeeves\CoreBundle\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 *
 * @ORM\Table(name="test")
 * @ORM\Entity()
 */
class TestEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     * @Assert\NotBlank(
     *     message="name.empty"
     * )
     */
    public $name;
}
