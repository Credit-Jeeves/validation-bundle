<?php
namespace CreditJeeves\TestBundle\NetConnect\Traits;

use CreditJeeves\DataBundle\Entity\User;
use RuntimeException;

trait CreditProfileTest
{
    protected function getFixturesDir()
    {
        return __DIR__ . '/../../Resources/NetConnect/CreditProfile/';
    }

    protected function getResponse($tenant)
    {
        switch ($tenant->getEmail()) {
            case 'emilio@example.com':
                return file_get_contents($this->getFixturesDir() . 'emilio.xml');
            case 'test@email.ru':
                return file_get_contents($this->getFixturesDir() . 'emilio.xml');
            case 'marion@example.com':
                return file_get_contents($this->getFixturesDir() . 'marion.xml');
            case 'alex@example.com':
                return file_get_contents($this->getFixturesDir() . 'alex.xml');
            case 'mamazza@example.com':
                return file_get_contents($this->getFixturesDir() . 'mamazza.xml');
            case 'john@example.com':
                return file_get_contents($this->getFixturesDir() . 'john.xml');
            case 'app14@example.com':
                return file_get_contents($this->getFixturesDir() . 'app14.xml');
            case 'robert@example.com':
                return file_get_contents($this->getFixturesDir() . 'robert.xml');
            case 'alexey.karpik+app1334753295955955@gmail.com':
                return file_get_contents($this->getFixturesDir() . 'alexey.karpik.xml');
        }
        throw new RuntimeException(sprintf('Please add fixture for user %s', $tenant->getEmail()));
    }

    public function getResponseOnUserData(User $user)
    {
        $this->composeRequest($this->createRequestOnUserData($user));

        return $this->createResponse($this->getResponse($user));
    }
}
