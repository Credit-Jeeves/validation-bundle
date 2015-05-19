<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ImportBaseAbstract extends BaseTestCase
{
    protected function waitReviewAndPost()
    {
        $this->session->wait(
            10000,
            "$('.overlay-trigger').length > 0"
        );

        $this->session->wait(
            21000,
            "$('.overlay-trigger').length <= 0"
        );

        $this->session->wait(
            10000,
            "$('.submitImportFile>span').is(':visible')"
        );
    }

    protected function waitRedirectToSummaryPage()
    {
        $this->session->wait(
            15000,
            "$('#summaryList').length > 0"
        );
    }
}
