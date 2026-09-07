<?php

namespace App\Enums;

enum DailyReportStatus: string
{
    case Draft = 'draft';
    case Locked = 'locked';
}
