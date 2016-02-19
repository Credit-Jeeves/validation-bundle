<?php

namespace RentJeeves\AdminBundle\Tests\Functional\Controller;

use RentJeeves\AdminBundle\Controller\RentalReportController;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class RentalReportControllerCase extends BaseTestCase
{
    /**
     * @test
     * @dataProvider provideLinkIds
     */
    public function shouldShowLateReportAndSendEmails($linkId)
    {
        $this->load(true);
        $this->setDefaultSession('selenium2');
        $this->login('admin@creditjeeves.com', 'P@ssW0rd');
        $this->assertNotNull($reviewLateReportLink = $this->page->find('css', $linkId));

        $reviewLateReportLink->click();

        $this->assertNotNull(
            $form = $this->page->find('css', '#rental_report'),
            'Rental report form not found'
        );

        $today = new \DateTime();
        $threeMonthsAgo = new \DateTime('-3 month');

        $this->fillForm(
            $form,
            [
                'rental_report_month_month' => $today->format('n'),
                'rental_report_month_year' => $today->format('Y'),
                'rental_report_startDate' => $threeMonthsAgo->format('Y-m-d'),
                'rental_report_endDate' => $today->format('Y-m-d'),
            ]
        );

        $this->assertNotNull(
            $submit = $this->page->find('css', '#rental_report input[type=submit]'),
            'Submit button not found'
        );

        $submit->click();

        $this->assertNotNull(
            $form = $this->page->find('css', '#form_send_notification'),
            'Form for sending notification not found'
        );

        $this->assertNotNull(
            $dataRows = $this->page->findAll(
                'css',
                '#form_send_notification table>tbody>tr.sonata-ba-list-field-record'
            ),
            'No report data rows found'
        );

        $this->assertCount(1, $dataRows, 'Late Report should have 1 row');
        $this->assertNotNull($listBatchCheckbox = $this->page->find('css', '#list_batch_checkbox'));
        $listBatchCheckbox->click();

        $this->clearEmail();
        // Send notification to Landlord
        $this->assertNotNull($btnSendNotification = $this->page->find('css', '#button_send_notification'));
        $this->assertNotNull($actionSendEmail = $this->page->find('css', '#action_send_notification'));
        $actionSendEmail->setValue(RentalReportController::NOTIFICATION_LANDLORD);

        // Selenium fails when sees alerts, so this is a hack to override alerts.
        $this->session->evaluateScript(
            'window.alert = function (message) {console.log(message);}'
        );

        $btnSendNotification->click();
        $this->session->wait($this->timeout, '!$(".overlay").is(":visible")');
        $this->assertCount(1, $emails = $this->getEmails());
        $landlordMessage = $this->getEmailReader()->getEmail($emails[0])->getMessage('text/html');
        $this->assertEquals(
            'Action Required for Rent Reporting',
            $landlordMessage->getSubject()
        );
        $this->assertContains('TIMOTHY APPLEGATE, t0013534', $landlordMessage->getBody());

        // Send notification to tenant
        $actionSendEmail->setValue(RentalReportController::NOTIFICATION_TENANT);
        $btnSendNotification->click();
        $this->session->wait($this->timeout, '!$(".overlay").is(":visible")');
        $this->assertCount(2, $emails = $this->getEmails());
        $tenantMessage = $this->getEmailReader()->getEmail($emails[1])->getMessage('text/html');
        $this->assertEquals(
            'Action Required for Rent Reporting',
            $tenantMessage->getSubject()
        );
        $this->assertContains(
            sprintf('you are missing a payment for ', $today->format('F')),
            $tenantMessage->getBody()
        );
        $this->logout();
    }

    /**
     * @return array
     */
    public function provideLinkIds()
    {
        return [
            ['#block_rental_report_experian'],
            ['#block_rental_report_transunion']
        ];
    }
}
