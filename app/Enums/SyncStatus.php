<?php

namespace App\Enums;

enum SyncStatus: string
{
    case Pending   = 'pending';
    case Running   = 'running';
    case Completed = 'completed';
    case Failed    = 'failed';
}
