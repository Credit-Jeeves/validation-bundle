<?php

namespace RentJeeves\ImportBundle\LeaseImport\Fields;

class LeaseFields
{
    const FIRST = 1;

    const BALANCE = 1;

    const RENT = 2;

    const DUE_DATE = 3;

    const FINISH_AT = 4;

    const START_AT = 5;

    const LAST = 5;

    //Hexadecimal
    const UPDATE_MASK_MATCHED = 0x16; // rent, balance, finishAt

    const UPDATE_MASK_NEW = 0x3e; // all
}
