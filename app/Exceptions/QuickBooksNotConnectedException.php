<?php

namespace App\Exceptions;

use RuntimeException;

class QuickBooksNotConnectedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('QuickBooks is not connected. Please connect via the admin panel.');
    }
}
