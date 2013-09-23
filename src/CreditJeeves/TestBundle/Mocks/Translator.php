<?php
namespace CreditJeeves\TestBundle\Mocks;

trait Translator
{
    public function getTranslator()
    {
        $translator = $this->getMock(
            '\Symfony\Bundle\FrameworkBundle\Translation\Translator',
            array('trans'),
            array(),
            '',
            false
        );
        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));
        return $translator;
    }
}
