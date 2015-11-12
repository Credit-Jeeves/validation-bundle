<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class ImportBaseAbstract extends BaseTestCase
{
    /**
     * @return array
     */
    protected function getParsedTrsByStatus()
    {
        $result = [];
        $tds = $this->page->findAll(
            'css',
            '#importTable>tbody>tr>td.import_status_text'
        );

        foreach ($tds as $td) {
            $result[$td->getHtml()][] = $td->getParent();
        }

        return $result;
    }

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
            1500000,
            "$('#summaryList').length > 0"
        );
    }
}
