<?php

namespace RentJeeves\ExternalApiBundle\Services\Binlist;

use RentJeeves\DataBundle\Entity\DebitCardBinlist;
use RentJeeves\DataBundle\Entity\DebitCardBinlistRepository;

/**
 * Service binlist.card
 */
class BinlistCard
{
    const IIN_LENGTH = 6;

    const TYPE_DEBIT = 'DEBIT';

    /**
     * @var DebitCardBinlistRepository
     */
    protected $debitCardRepository;

    /**
     * @param DebitCardBinlistRepository $repo
     */
    public function __construct(DebitCardBinlistRepository $repo)
    {
        $this->debitCardRepository = $repo;
    }

    /**
     * @param string $cardNumber
     * @return bool
     */
    public function isLowDebitFee($cardNumber)
    {
        $iin = $this->extractIin($cardNumber);
        /** @var DebitCardBinlist $binlistDebitCard */
        $binlistDebitCard = $this->debitCardRepository->getDebitCardByIin($iin);
        if (null !== $binlistDebitCard) {
            return $binlistDebitCard->getBinlistBank()->getLowDebitFee();
        }

        return false;
    }

    /**
     * @param string $cardNumber
     * @return string
     */
    protected function extractIin($cardNumber)
    {
        if (strlen($cardNumber) < self::IIN_LENGTH) {
            throw new \InvalidArgumentException(sprintf(
                '"%s": CardNumber length less than %s symbols',
                $cardNumber,
                self::IIN_LENGTH
            ));
        }

        return substr($cardNumber, 0, 6);
    }
}
