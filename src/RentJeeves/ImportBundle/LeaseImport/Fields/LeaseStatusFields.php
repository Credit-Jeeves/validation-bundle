<?php

namespace RentJeeves\ImportBundle\LeaseImport\Fields;

class LeaseStatusFields
{
    //new : lease created
    const NEW_ONE = '1';
    //match : lease updated
    const MATCH = '2';
    //skip : do not update sytem with this lease
    const SKIP = 3;
    //error: an error occurred. see error_messages
    const ERROR = 4;
}
