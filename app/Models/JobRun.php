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
        'finished_at',
        'seconds_taken',
        'route_code'
    ];

    protected $dates = [
        'started_at',
        'finished_at',
    ];

    public function getCurrentlyRunningAttribute(): bool
    {
        return $this->started_at && !$this->finished_at || $this->started_at > $this->finished_at;
    }
}
