<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

class AttributeGeneratorWebCase extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function methodsShouldbeArray()
    {
        $web = new AttributeGeneratorWeb();

        foreach(get_class_methods($web) as $currentMethod){
            $methodReturn = $web->$currentMethod();
            $this->assertInternalType('array', $methodReturn, $currentMethod ." should be array");
        }
    }

}
 