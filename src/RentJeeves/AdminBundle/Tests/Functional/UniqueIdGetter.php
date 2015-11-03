<?php

namespace RentJeeves\AdminBundle\Tests\Functional;

trait UniqueIdGetter
{
    /**
     *
     * @return string
     */
    protected function getUniqueId()
    {
        $this->assertNotNull($form = $this->page->find('css', 'form'), 'Form should be present');
        $action = $form->getAttribute('action');
        $uniqueId = substr($action, strpos($action, '=') + 1);
        $this->assertNotEmpty($uniqueId, '"uniqueId" cannot be empty');

        return $uniqueId;
    }
}
