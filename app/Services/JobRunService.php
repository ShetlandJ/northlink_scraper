<?php

namespace App\Services;

use App\Models\JobRun;

class JobRunService
{
    public function findByJobNameOrCreate(string $jobName, string $routeCode): JobRun
    {
        $jobRun = JobRun::where('job_name', $jobName)
            ->where('route_code', $routeCode)
            ->first();

        if ($jobRun) {
            return $jobRun;
        }

        return JobRun::create([
            'job_name' => $jobName,
            'route_code' => $routeCode,
        ]);
    }

    public function create(string $name, string $routeCode): JobRun
    {
        return JobRun::create([
            'job_name' => $name,
            'started_at' => now(),
            'route_code' => $routeCode,
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
