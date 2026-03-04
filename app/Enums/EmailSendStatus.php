<?php

namespace App\Enums;

enum EmailSendStatus: string
{
    case Pending = 'pending';
    case Sent    = 'sent';
    case Failed  = 'failed';
}
