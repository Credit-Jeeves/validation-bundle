<?php
namespace CreditJeeves\CoreBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as Base;

class Translator extends Base
{
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function getOption($option)
    {
        return $this->options[$option];
    }
}
