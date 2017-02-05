<?php

namespace Api\Table;

use Base\Table\BaseTable;

class AccountTable extends BaseTable
{   
    const TYPE_GROWSARI = 'GROWSARI';
    const TYPE_STORE = 'STORE';
    const TYPE_WAREHOUSE = 'WAREHOUSE';
    const TYPE_SHIPPER = 'SHIPPER';
    const TYPE_SALESPERSON = 'SALESPERSON';
    const TYPE_CALLCENTER = 'CALLCENTER';
    
    const ROLE_USER = 'USER';
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';
}
