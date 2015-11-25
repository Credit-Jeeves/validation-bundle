<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

/**
 * Trait should work just with AdminBundle Functional Test Cases
 */
trait AdminFormUniqueIdGetter
{
    /**
     * @return string
     */
    protected function getUniqueId()
    {
        $form = $this->getDomElement('form', 'Main admin form should be present');
        $action = $form->getAttribute('action');
        $uniqueId = substr($action, strpos($action, '=') + 1);
        $this->assertNotEmpty($uniqueId, '"uniqueId" cannot be empty');

        return $uniqueId;
    }
}
