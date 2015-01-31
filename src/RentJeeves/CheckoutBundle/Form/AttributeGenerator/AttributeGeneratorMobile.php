<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;


class AttributeGeneratorMobile extends AttributeGenerator implements AttributeGeneratorInterface {

    function amountAttrs()
    {
        return array_merge(parent::amountAttrs(),
            array(

            )
        );

    }

    function typeAttrs()
    {
        return array_merge(parent::amountAttrs(),
            array(
                'data-native-menu' => "false"
                //'data-enhanchment'=>"false",
                //'data-enhance'=>'false',
                //'data-native-menu'=>'true'
            )
        );
    }

    function dueDateAttrs()
    {
        return array_merge(
            parent::dueDateAttrs(), array('data-role'=>'date')
        );


    }

    function submitAttrs()
    {
        return array_merge(parent::submitAttrs(), array('style'=>'display:none'));
    }

    function paymentAccountAttrs(){
        return array_merge(parent::paymentAccountAttrs(), array('id'=>'paymentDropdown'));
    }

    function idAttrs(){
        return array_merge(parent::idAttrs(), array('id'=>'id'));
    }

}
