<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRun extends Model
{
    protected $table = 'job_run_details';

    protected $fillable = [
        'job_name',
        'started_at',
        'finished_at'
    ];

    protected $dates = [
        'started_at',
        'finished_at',
    ];
}
