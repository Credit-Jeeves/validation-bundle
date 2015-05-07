<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

class AttributeGeneratorMobile extends AttributeGenerator implements AttributeGeneratorInterface
{
    public function isMobile()
    {
        return true;
    }

    public function amountAttrs()
    {
        return array_merge(parent::amountAttrs(),
            array(
                'onchange'=>'updateTotal()'
            )
        );
    }

    public function amountOtherAttrs()
    {
        return array_merge(parent::amountOtherAttrs(),
            array(
                'onchange'=>'updateTotal()'
            )
        );
    }

    public function typeAttrs()
    {
        return array_merge(parent::typeAttrs(),
            array(
                'data-native-menu' => "false",
                'onchange' => "if (this.value=='recurring') {formType(false)} else {formType(true)}"
                //'data-enhanchment'=>"false",
                //'data-enhance'=>'false',
                //'data-native-menu'=>'true'
            )
        );
    }

    public function dueDateAttrs()
    {
        return array_merge(
            parent::dueDateAttrs(), array('data-role'=>'date','data-inline'=>"true")
        );

    }

    public function submitAttrs()
    {
        return array_merge(parent::submitAttrs(), array('style'=>'display:none'));
    }

    public function paymentAccountAttrs()
    {
        return array_merge(parent::paymentAccountAttrs(), array('id'=>'paymentDropdown'));
    }

    public function idAttrs()
    {
        return array_merge(parent::idAttrs(), array('id'=>'id'));
    }

    public function startDateAttrs()
    {
        return array_merge(
            parent::startDateAttrs(),
            array('data-role'=>'datebox','data-options'=>"{'mode':'calbox','useFocus':'true','useButton':'false'}",'onchange'=>"$('#dateConfirmed').popup();$('#dateConfirmed').popup('open');$('#selectedDate').html(this.value)")
        );
    }

    public function endsAttrs()
    {
        return array_merge(parent::endsAttrs(),array(
           'onchange'=>'if (this.value!="on") {whenCancelled(false)} else {whenCancelled(true)};'
        ));
    }

    public function frequencyAttrs()
    {
        return array_merge(parent::frequencyAttrs(),array(
            'onchange'=>'if (this.value!="monthly") {freqHide(false)} else {freqHide(true)};'
        ));
    }

}
