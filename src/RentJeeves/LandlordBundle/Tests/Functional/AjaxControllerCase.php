<?php
namespace RentJeeves\LandlordBundle\Tests\Functional;

use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyAddress;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class AjaxControllerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldUpdateGoogleReferenceWhenLandlordApproveContract()
    {
        $this->load(true);
        // Create address and property and invite landlord on tenant part
        $newProperty = new Property();
        $newPropertyAddress = new PropertyAddress();
        $newPropertyAddress->setNumber(1);
        $newPropertyAddress->setStreet('test');
        $newPropertyAddress->setCity('test');
        $newPropertyAddress->setState('test');
        $newPropertyAddress->setZip('test');
        $newPropertyAddress->setLat(40.7308364);
        $newPropertyAddress->setLong(-73.991567);

        $newProperty->setPropertyAddress($newPropertyAddress);

        $this->getEntityManager()->persist($newProperty);
        $this->getEntityManager()->flush();

        $this->getEntityManager()->refresh($newProperty);

        $this->setDefaultSession('selenium2');

        $this->login('tenant11@example.com', 'pass');
        $id = $newProperty->getId();
        $this->session->visit($this->getUrl() . 'property/invite/'. $id);
        $landlordEmailInput = $this->getDomElement(
            '#rentjeeves_publicbundle_invitetype_email',
            'Landlord Email Input not found.'
        );
        $landlordEmailInput->setValue('landlord1@example.com');
        $isSinglePropertyCheckbox = $this->getDomElement(
            '#rentjeeves_publicbundle_invitetype_is_single',
            'Checkbox "is Single" not found.'
        );
        $isSinglePropertyCheckbox->click();
        $addPropertyButton = $this->getDomElement('#register', 'Add property button not found.');
        $addPropertyButton->click();
        $this->assertNull(
            $newProperty->getPropertyAddress()->getGoogleReference(),
            'Google reference should be null until contract is approved by Landlord'
        );
        $this->logout();
        // approve contract
        $this->login('landlord1@example.com', 'pass');
        $this->session->visit($this->getUrl() . 'landlord/tenants');

        $this->chooseLinkSelect('searchFilter', 'street');
        $filterInput = $this->getDomElement('#searsh-field', 'Filter input not found.');
        $filterInput->setValue('test');
        $searchSubmitButton = $this->getDomElement('#search-submit', 'Search submit Button not found.');
        $searchSubmitButton->click();

        $this->session->wait(5000, '$(".properties-table tbody tr").length > 0');
        $contractApproveButton = $this->getDomElement('a.approve', 'Contract approve button not found.');
        $contractApproveButton->click();

        $amountApproveInput = $this->getDomElement('#amount-approve', 'Amount approve input not found.');
        $amountApproveInput->setValue(123);

        $approveTenantButton = $this->getDomElement('#approveTenant', 'Approve tenant button not found.');
        $approveTenantButton->click();

        $this->session->wait(15000, '$(".overlay").is(":hidden")');

        $property = $this->getEntityManager()->getRepository('RjDataBundle:Property')->find($id);
        $this->assertNotNull(
            $property->getPropertyAddress()->getGoogleReference(),
            'Google reference should be updated.'
        );
    }
}
