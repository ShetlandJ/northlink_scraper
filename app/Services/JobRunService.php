<?php

namespace App\Services;

use App\Models\JobRun;

class JobRunService
{
    public function findByJobNameOrCreate($jobName): JobRun
    {
        $jobRun = JobRun::where('job_name', $jobName)->first();

        if ($jobRun) {
            return $jobRun;
        }

        return JobRun::create([
            'job_name' => $jobName,
        ]);
    }

    public function create(string $name): JobRun
    {
        return JobRun::create([
            'job_name' => $name,
            'started_at' => now(),
        ]);
    }

    public function startJob(JobRun $jobRun): JobRun
    {
        $jobRun->started_at = now();
        $jobRun->save();

        return $jobRun;
    }

    public function endJob(JobRun $jobRun): void
    {
        $jobRun->update([
            'finished_at' => now(),
            'seconds_taken' => $jobRun->started_at->diffInSeconds(now()),
        ]);
    }
}
