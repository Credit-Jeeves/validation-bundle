<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use WebDriver\Exception\UnexpectedAlertOpen;

class VirtualTerminalCase extends BaseTestCase
{
    /**
     * @test
     */
    public function charge()
    {
        $this->setDefaultSession('selenium2');
        $this->load(true);

        /** @var Landlord $landlord */
        $landlord = $this->getEntityManager()->getRepository('RjDataBundle:Landlord')->findOneBy(
            ['email' => 'landlord1@example.com']
        );

        /** @var Group $group */
        $group = $this->getEntityManager()->getRepository('DataBundle:Group')->findOneBy(
            ['name' => 'Test Rent Group']
        );

        $orderQuery = $this->getEntityManager()
            ->getRepository('DataBundle:Order')
            ->createQueryBuilder('o')
            ->innerJoin('o.operations', 'p')
            ->where('p.group = :group')
            ->andWhere('o.user = :user')
            ->andWhere('p.type = :type')
            ->setParameter('group', $group)
            ->setParameter('user', $landlord)
            ->setParameter('type', OperationType::CHARGE)
            ->getQuery();

        /** @var Order[] $ordersBefore */
        $ordersBefore = $orderQuery->execute();

        $this->login('landlord1@example.com', 'pass');
        $this->page->clickLink('common.account');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->page->clickLink('settings.deposit');
        $this->session->wait($this->timeout, "typeof $ != 'undefined'");
        $this->session->wait($this->timeout, "$('.add-accoun').is(':visible')");
        $this->page->clickLink('add.account');
        $this->assertNotNull($form = $this->page->find('css', '#billingAccountType'));

        $this->fillForm(
            $form,
            [
                'billingAccountType_nickname'         => "mary",
                'billingAccountType_PayorName'        => "mary stone",
                'billingAccountType_AccountNumber_AccountNumber'    => "123245678",
                'billingAccountType_AccountNumber_AccountNumberAgain'    => "123245678",
                'billingAccountType_RoutingNumber'    => "062202574",
                'billingAccountType_ACHDepositType_0' => true,
                'billingAccountType_isActive'         => true,
            ]
        );
        $this->assertNotNull($save = $this->page->find('css', '#save_payment'));
        $save->click();
        $this->session->wait(
            $this->timeout + 20000,
            "!$('#billingAccountType').is(':visible')"
        );
        $this->session->wait(
            $this->timeout,
            "$('.properties-table tbody tr').length"
        );
        $this->assertNotNull($account = $this->page->findAll('css', '.properties-table>tbody>tr>td'));
        $this->assertEquals('mary (settings.payment_account.active)', $account[0]->getText());
        $this->logout();

        $this->login('admin@creditjeeves.com', 'P@ssW0rd');

        $this->session->visit(
            sprintf(
                $this->getUrl() . 'admin/rj/group/%s/edit',
                $group->getId()
            )
        );

        $this->page->clickLink('Virtual terminal');

        $this->assertNotNull($form = $this->page->find('css', '.form-horizontal'));

        $this->assertNotNull($amountField = $this->page->find('css', 'form input.terminal_amount'));
        $this->assertNotNull($customField = $this->page->find('css', 'form input.terminal_custom'));

        $this->fillForm(
            $form,
            [
                $amountField->getAttribute('id') => 99,
                $customField->getAttribute('id') => 'Test Charge'
            ]
        );

        $this->assertCount(2, $links = $this->page->findAll('css', 'div.form-actions > a.btn'));

        $links[1]->click();

        $this->acceptAlert();

        $dialogMessage = '';

        try {
            $this->session->wait($this->timeout + 15000);
        } catch (UnexpectedAlertOpen $e) {
            $dialogMessage = $e->getMessage();
        }

        $this->assertContains('Payment succeed', $dialogMessage, 'Payment is not successful');

        /** @var Order[] $ordersAfter */
        $ordersAfter = $orderQuery->execute();

        $this->assertEquals(count($ordersBefore) + 1, count($ordersAfter), 'Order hasn\'t created.');

        $this->assertEquals(
            OrderStatus::COMPLETE,
            end($ordersAfter)->getStatus(),
            sprintf(
                'Order has status "%s" instead "%s"',
                end($ordersAfter)->getStatus(),
                OrderStatus::COMPLETE
            )
        );
    }
}
