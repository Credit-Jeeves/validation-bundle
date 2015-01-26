<?php

namespace CreditJeeves\DataBundle\Entity;

/**
 * Interface ReportSummaryInterface
 *
 * This interface is used by the ComponentBundle controllers to generate the RentTrack /summary view.
 *
 * @package CreditJeeves\DataBundle\Entity
 */
interface ReportSummaryInterface
{
    /**
     * @return Float The balance of all revolving accounts. (i.e. 12345.67)
     */
    public function getBalanceRevolvingAccounts();

    /**
     * @return Float The balance of all mortgage accounts. (i.e. 12345.67)
     */
    public function getBalanceMortgageAccounts();

    /**
     * @return Float The balance of all installment accounts. (i.e. 12345.67)
     */
    public function getBalanceInstallmentAccounts();

    /**
     * @return Float The balance of all accounts turned over to collections. (i.e. 12345.67)
     */
    public function getBalanceOpenCollectionAccounts();

    /**
     * @return Float Monthly payment amounts due each month as reported by the creditor (i.e. 12345.67)
     */
    public function getTotalMonthlyPayments();

    /**
     * @return Integer Total number of accounts/tradelines
     */
    public function getTotalAccounts();

    /**
     * @return Integer Total number of open accounts/tradelines
     */
    public function getTotalOpenAccounts();

    /**
     * @return Integer Total number of closed accounts/tradelines
     */
    public function getTotalClosedAccounts();

    /**
     * @return Integer Total number of derogatory accounts/tradelines
     */
    public function getTotalDerogatoryAccounts();

    /**
     * @return Integer Total number of accounts/tradelines turned over to collections
     */
    public function getTotalOpenCollectionAccounts();

    /**
     * @return Integer Total number of accounts/tradelines obtained from public records
     */
    public function getTotalPublicRecords();

    /**
     * @return Integer Percentage of credit used
     */
    public function getUtilization();

    /**
     * @return Integer Number of companies that viewed the consumer’s credit file over the last inquiry period
     */
    public function getNumberOfInquiries();

    /**
     * The window of time in which the value getNumberOfInquries was captured.
     *
     * @return HardInquiriesPeriod
     */
    public function getInquiriesPeriod();

    /**
     * @return Float
     */
    public function getOldestTradelineInYears();

    /**
     * The name of the credit bureau that generated this report
     *
     * @return String
     */
    public function getBureauName();
}
