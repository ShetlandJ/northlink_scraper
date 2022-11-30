<?php

namespace App\Services;

use App\Models\JobRun;

class JobRunService
{
    public function create(string $name): JobRun
    {
        return JobRun::create([
            'job_name' => $name,
            'started_at' => now(),
        ]);
    }

    public function endJob(JobRun $jobRun): void
    {
        $jobRun->update([
            'finished_at' => now(),
            'seconds_taken' => $jobRun->started_at->diffInSeconds(now()),
        ]);
    }
}
