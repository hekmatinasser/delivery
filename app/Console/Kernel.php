<?php

namespace App\Console;

use Carbon\Carbon;
use App\Models\Constraint;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $schedule->call(function () use ($now) {
           $constraints = Constraint::where('constraint_end_time','<=', $now)->get();
           foreach($constraints as $constraint){
            $constraint->update([
                'constraint_status'=> 3
            ]);
           }
        })->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
