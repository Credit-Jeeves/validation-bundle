<?php

namespace RentJeeves\ExternalApiBundle\Command;

use JMS\Serializer\Serializer;
use RentJeeves\DataBundle\Entity\BinlistBank;
use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\ExternalApiBundle\Services\Binlist\BinlistSource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\CoreBundle\Command\BaseCommand;

class UpdateDebitCardBinlistCommand extends BaseCommand
{
    const DEBIT_TYPE = 'DEBIT';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('api:binlist:update-data')
            ->setDescription('Update DebitCardBinList data.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getLogger();
        $logger->info('Updating binlist data ...');
        $repo = $this->getBinlistRepository();
        /** @var BinlistSource $binlistSource */
        $binlistSource = $this->getContainer()->get('binlist.source');
        $binlistData = $binlistSource->loadBinlistData();

        foreach ($binlistData as $debitCardData) {
            try {
                $cardType = $this->getFieldValue($debitCardData, 'card_type', true);
                if (self::DEBIT_TYPE === $cardType) {
                    /** @var DebitCardBinlist $debitCard */
                    $debitCard = $repo->findOneByIin($this->getFieldValue($debitCardData, 'iin', true));
                    if (null !== $debitCard) {
                        $this->updateDebitCard($debitCard, $debitCardData);
                    } else {
                        $this->createDebitCard($debitCardData);
                    }
                }
            } catch (\Exception $e) {
                $logger->error(sprintf(
                    'Processing debit card data failed with error: "%s". Data: %s',
                    $e->getMessage(),
                    implode(',', $debitCardData)
                ));
            }
        }
        $logger->info('Updating binlist data finished!');
    }

    /**
     * @param DebitCardBinlist $debitCard
     * @param array $debitCardData
     */
    protected function updateDebitCard(DebitCardBinlist $debitCard, array $debitCardData)
    {
        $bankName = $debitCard->getBinlistBank()->getBankName();
        if ($bankName !== $inputBankName = $this->getFieldValue($debitCardData, 'bank_name', true)) {
            $bank = $this->getBinlistBank($inputBankName);
            $debitCard->setBinlistBank($bank);
        }
        if ($debitCard->getBankCity() !== $inputBankCity = $this->getFieldValue($debitCardData, 'bank_city')) {
            $debitCard->setBankCity($inputBankCity);
        }
        if ($debitCard->getBankPhone() !== $inputBankPhone = $this->getFieldValue($debitCardData, 'bank_phone')) {
            $debitCard->setBankPhone($inputBankPhone);
        }
        if ($debitCard->getBankUrl() !== $inputBankUrl = $this->getFieldValue($debitCardData, 'bank_url')) {
            $debitCard->setBankUrl($inputBankUrl);
        }
        if ($debitCard->getCardBrand() !== $inputCardBrand = $this->getFieldValue($debitCardData, 'card_brand')) {
            $debitCard->setCardBrand($inputCardBrand);
        }
        if ($debitCard->getCardSubBrand() !==
            $inputCardSubBrand = $this->getFieldValue($debitCardData, 'card_sub_brand')
        ) {
            $debitCard->setCardSubBrand($inputCardSubBrand);
        }
        if ($debitCard->getCardType() !== $inputCardType = $this->getFieldValue($debitCardData, 'card_type')) {
            $debitCard->setCardType($inputCardType);
        }
        if ($debitCard->getCardCategory() !==
            $inputCardCategory = $this->getFieldValue($debitCardData, 'card_category')
        ) {
            $debitCard->setCardCategory($inputCardCategory);
        }
        if ($debitCard->getCountryCode() !== $inputCountryCode = $this->getFieldValue($debitCardData, 'country_code')) {
            $debitCard->setCountryCode($inputCountryCode);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param array $debitCardData
     */
    protected function createDebitCard(array $debitCardData)
    {
        /** @var DebitCardBinlist $debitCard */
        $debitCard = $this->getSerializer()->deserialize(
            json_encode($debitCardData),
            'RentJeeves\DataBundle\Entity\DebitCardBinlist',
            'json'
        );
        $bank = $this->getBinlistBank($this->getFieldValue($debitCardData, 'bank_name', true));
        $debitCard->setBinlistBank($bank);
        $this->getEntityManager()->persist($debitCard);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $bankName
     * @return BinlistBank
     */
    protected function getBinlistBank($bankName)
    {
        if (true === empty($bankName)) {
            throw new \InvalidArgumentException('Bank name can not be empty');
        }

        $bank = $this->getBinlistBankRepository()->findOneByBankName($bankName);
        if (null === $bank) {
            $bank = new BinlistBank();
            $bank->setBankName($bankName);
            $this->getEntityManager()->persist($bank);
            $this->getLogger()->info(sprintf('Added a new bank: %s', $bankName));
        }

        return $bank;
    }

    /**
     * @param array $data
     * @param string $fieldName
     * @param bool $throwException
     * @return null|string
     */
    protected function getFieldValue(array $data, $fieldName, $throwException = false)
    {
        if (isset($data[$fieldName])) {
            return trim($data[$fieldName]);
        }

        if ($throwException) {
            throw new \InvalidArgumentException(sprintf('Field %s is not set', $fieldName));
        }

        return null;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getBinlistRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:DebitCardBinlist');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getBinlistBankRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:BinlistBank');
    }

    /**
     * @return Serializer
     */
    protected function getSerializer()
    {
        return $this->getContainer()->get('jms_serializer');
    }
}
