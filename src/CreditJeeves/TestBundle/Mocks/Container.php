<?php
namespace CreditJeeves\TestBundle\Mocks;

trait Container
{
    /**
     * @param mixed $return
     *
     * @return mixed
     */
    public function get($return)
    {
        $container = $this->getMock(
            '\Symfony\Component\DependencyInjection\Container',
            array('get'),
            array(),
            '',
            false
        );
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue($return));
        return $container;
    }
}
