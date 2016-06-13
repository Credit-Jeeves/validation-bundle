<?php

namespace RentJeeves\ImportBundle\LeaseImport\Fields;

class ResidentFields
{
    const FIRST = 1;

    const EXTERNAL_RESIDENT_ID = 1;

    const EMAIL = 2;

    const FIRST_NAME = 3;

    const LAST_NAME = 4;

    const PHONE = 5;

    const DATE_OF_BIRTH = 6;

    const RESIDENT_STATUS = 7;

    const LAST = 7;

    //Hexadecimal
    const UPDATE_MASK_MATCHED = 0x84; // email

    const UPDATE_MASK_NEW = 0xfe; // all
}
