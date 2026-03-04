<?php

namespace App\Enums;

enum SyncDirection: string
{
    case Pull = 'pull';
    case Push = 'push';
}
