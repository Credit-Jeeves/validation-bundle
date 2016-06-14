<?php

namespace RentJeeves\ImportBundle\LeaseImport\Fields;


class ResidentStatusFields
{
    /**
     *  email invite was sent successfully
     */
    const INVITED = 1;

    /**
     *  when group settings disables inviting'
     */
    const NOT_INVITED = 2;

    /**
     * lease created but no invite send due to missing email address
     */
    const NO_EMAIL = 3;

    /**
     * lease created but no invite send due to bad or blacklisted email address
     */
    const BAD_EMAIL = 4;

    /**
     * an error occurred. see error_messages"
     */
    const ERROR = 5;
}
