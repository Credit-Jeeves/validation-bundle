<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;


class AttributeGeneratorMobile extends AttributeGenerator implements AttributeGeneratorInterface {

    function isMobile(){
        return true;
    }

    function amountAttrs()
    {
        return array_merge(parent::amountAttrs(),
            array(
                'onchange'=>'updateTotal()'
            )
        );
    }

    function typeAttrs()
    {
        return array_merge(parent::typeAttrs(),
            array(
                'data-native-menu' => "false",
                'onchange' => "if(this.value=='recurring'){formType(false)}else{formType(true)}"
                //'data-enhanchment'=>"false",
                //'data-enhance'=>'false',
                //'data-native-menu'=>'true'
            )
        );
    }

    function dueDateAttrs()
    {
        return array_merge(
            parent::dueDateAttrs(), array('data-role'=>'date','data-inline'=>"true")
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

    function startDateAttrs(){
        return array_merge(
            parent::startDateAttrs(),
            array('data-role'=>'datebox','data-options'=>"{'mode':'calbox','useFocus':'true','useButton':'false'}",'onchange'=>"$('#dateConfirmed').popup();$('#dateConfirmed').popup('open');$('#selectedDate').html(this.value)")
        );
    }

    function endsAttrs(){
        return array_merge(parent::endsAttrs(),array(
           'onchange'=>'if(this.value!="on"){whenCancelled(false)}else{whenCancelled(true)};'
        ));
    }

    function frequencyAttrs(){
        return array_merge(parent::frequencyAttrs(),array(
            'onchange'=>'if(this.value!="monthly"){freqHide(false)}else{freqHide(true)};'
        ));
    }

}
