<?php

namespace RentJeeves\CoreBundle\Twig;

class FormExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'form_radio' => new \Twig_Function_Node(
                'Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode',
                array('is_safe' => array('html'))
            ),
        );
    }

    public function getName()
    {
        return 'core_form_extension';
    }
}
