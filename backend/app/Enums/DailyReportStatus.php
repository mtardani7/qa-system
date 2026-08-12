<?php

namespace App\Enums;

enum DailyReportStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Locked = 'locked';
    case Cancelled = 'cancelled';
}
