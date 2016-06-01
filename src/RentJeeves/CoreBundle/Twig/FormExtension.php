<?php

namespace RentJeeves\CoreBundle\Twig;

class FormExtension extends \Twig_Extension
{
    /**
     * Used only for payment type fee on mobile app
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'form_payment_type_fee_radio' => new \Twig_Function_Node(
                'Symfony\Bridge\Twig\Node\RenderBlockNode',
                ['is_safe' => array('html')]
            ),
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'core_form_extension';
    }
}
