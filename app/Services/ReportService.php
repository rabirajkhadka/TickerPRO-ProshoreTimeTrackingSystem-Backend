<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ReportService
{
    protected User $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * @param array $validated
     * @return void
     */
    public function getUsersReport(array $validated)
    {
        $users = $this->user->whereIn('id', Arr::get($validated, 'user_id'))
            ->with(['timelogs' => function ($query) use ($validated) {
                $timelogQuery = $query->whereBetween('start_date', [Arr::get($validated, 'start_date'), Arr::get($validated, 'end_date')])
                    ->whereBetween('end_date', [Arr::get($validated, 'start_date'), Arr::get($validated, 'end_date')])
                    ->where('billable', 1)
                    ->whereHas('project', function ($query) {
                        $query->where('billable', 1);
                    })
                    ->with('project.client');

                if ($validated['project_id'] !== null) {
                    $timelogQuery->where('project_id', Arr::get($validated, 'project_id'));
                }
            }])->get();

        $report = $this->getUsersReportDetails($validated, $users);
        return $report;
    }


   /**
     * @param array $validated
     * @param object $users
     * @return void
     */
    public function getUsersReportDetails(array $validated, object $users)
    {
        {
            $reports = $users->map(function ($user) use ($validated) {
                $userTotalTime = $user->timelogs->sum(function ($timelog) {
                    $startDateTime = Carbon::parse($timelog->start_date . ' ' . $timelog->started_time);
                    $endDateTime = Carbon::parse($timelog->end_date . ' ' . $timelog->ended_time);
                    return $endDateTime->diffInMinutes($startDateTime);
                });
                $activites= $this->getUserActivity($validated, $user);
                return [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'client' => $user->timelogs->pluck('project.client.client_name')->first(),
                    'total_time' => intdiv($userTotalTime, 60) . 'hrs ' . ' ' . ($userTotalTime % 60) . 'min',
                    'activities' => $activites
                ];
            });
            return ($reports->all());
            }
    }


   /**
     * @param array $validated
     * @param object $user
     * @return void
     */
    public function getUserActivity(array $validated, object $user)
    {
        $activities = $user->timelogs->map(function ($timelog) use ($validated) {
            $startDateTime = Carbon::parse($timelog->start_date . ' ' . $timelog->started_time);
            $endDateTime = Carbon::parse($timelog->end_date . ' ' . $timelog->ended_time);
            $totalTime = $endDateTime->diffInMinutes($startDateTime);
            $activity = [
                'activity' => $timelog->activity_name,
                'total_time' =>  intdiv($totalTime, 60) . 'hrs ' . ' ' . ($totalTime % 60) . 'min',
                'project' => $timelog->project->project_name
            ];
            if ($validated['project_id'] === null)
                $activity += ['project' => $timelog->project->project_name];
            return $activity;
        });
        return $activities;
    }
}