<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::creating(function (object $model): void {
            $userId = Auth::id();
            $model->created_by ??= $userId;
            $model->updated_by ??= $userId;
        });

        static::updating(function (object $model): void {
            $model->updated_by = Auth::id();
        });
    }
}
