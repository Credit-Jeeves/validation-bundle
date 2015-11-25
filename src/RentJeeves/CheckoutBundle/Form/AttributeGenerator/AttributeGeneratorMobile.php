<?php

namespace RentJeeves\CheckoutBundle\Form\AttributeGenerator;

class AttributeGeneratorMobile extends AttributeGenerator
{
    public static function isMobile()
    {
        return true;
    }

    public static function amountAttrs()
    {
        return array_merge(parent::amountAttrs(), [ 'onchange'=>'updateTotal()' ]);
    }

    public static function amountOtherAttrs()
    {
        return array_merge(parent::amountOtherAttrs(), [ 'onchange'=>'updateTotal()' ]);
    }

    public static function typeAttrs()
    {
        return array_merge(
            parent::typeAttrs(),
            [
                'data-native-menu' => "false",
                'onchange' => "if (this.value=='recurring') {formType(false)} else {formType(true)}"
            ]
        );
    }

    public static function dueDateAttrs()
    {
        return array_merge(parent::dueDateAttrs(), array('data-role' => 'date', 'data-inline' => 'true'));
    }

    public static function submitAttrs()
    {
        return array_merge(parent::submitAttrs(), [ 'style' => 'display:none' ]);
    }

    public static function paymentAccountAttrs()
    {
        return array_merge(parent::paymentAccountAttrs(), [ 'id' => 'paymentDropdown' ]);
    }

    public static function idAttrs()
    {
        return array_merge(parent::idAttrs(), [ 'id' => 'id' ]);
    }

    public static function startDateAttrs($isPastCutoffTime = false)
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

    public static function endsAttrs()
    {
        return array_merge(
            parent::endsAttrs(),
            [ 'onchange' => 'if (this.value!="on") {whenCancelled(false)} else {whenCancelled(true)};' ]
        );
    }

    public static function frequencyAttrs()
    {
        return array_merge(
            parent::frequencyAttrs(),
            [ 'onchange' => 'if (this.value!="monthly") {freqHide(false)} else {freqHide(true)};' ]
        );
    }
}
