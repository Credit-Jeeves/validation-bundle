<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

class AttributeGeneratorMobile extends AttributeGenerator
{
    public function isMobile()
    {
        return true;
    }

    public function amountAttrs()
    {
        return array_merge(parent::amountAttrs(), [ 'onchange'=>'updateTotal()' ]);
    }

    public function amountOtherAttrs()
    {
        return array_merge(parent::amountOtherAttrs(), [ 'onchange'=>'updateTotal()' ]);
    }

    public function typeAttrs()
    {
        return array_merge(
            parent::typeAttrs(),
            [
                'data-native-menu' => "false",
                'onchange' => "if (this.value=='recurring') {formType(false)} else {formType(true)}"
            ]
        );
    }

    public function dueDateAttrs()
    {
        return [ parent::dueDateAttrs(), array('data-role' => 'date', 'data-inline' => 'true') ];
    }

    public function submitAttrs()
    {
        return array_merge(parent::submitAttrs(), [ 'style' => 'display:none' ]);
    }

    public function paymentAccountAttrs()
    {
        return array_merge(parent::paymentAccountAttrs(), [ 'id' => 'paymentDropdown' ]);
    }

    public function idAttrs()
    {
        return array_merge(parent::idAttrs(), [ 'id' => 'id' ]);
    }

    public function startDateAttrs($isPastCutoffTime = false)
    {
        return array_merge(
            parent::startDateAttrs($isPastCutoffTime),
            [
                'data-role' => 'datebox',
                'data-options' => "{'mode':'calbox','useFocus':'true','useButton':'false'}",
                'onchange' =>
                    "$('#dateConfirmed').popup();".
                    "$('#dateConfirmed').popup('open');".
                    "$('#selectedDate').html(this.value)"
            ]
        );
    }

    public function endsAttrs()
    {
        return array_merge(
            parent::endsAttrs(),
            [ 'onchange' => 'if (this.value!="on") {whenCancelled(false)} else {whenCancelled(true)};' ]
        );
    }

    public function frequencyAttrs()
    {
        return array_merge(
            parent::frequencyAttrs(),
            [ 'onchange' => 'if (this.value!="monthly") {freqHide(false)} else {freqHide(true)};' ]
        );
    }
}
