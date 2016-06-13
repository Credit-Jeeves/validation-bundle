<?php

namespace RentJeeves\ImportBundle\LeaseImport\Fields;

class LeaseFields
{
    const FIRST = 0;

    const BALANCE = 0;

    const RENT = 1;

    const DUE_DATE = 2;

    const FINISH_AT = 3;

    const START_AT = 4;

    const LAST = 4;

    //Hexadecimal
    const UPDATE_MASK_MATCHED = 0xb; // rent, balance, finishAt

    const UPDATE_MASK_NEW = 0x1f; // all
}

