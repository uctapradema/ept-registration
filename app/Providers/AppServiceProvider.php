<?php

namespace App\Providers;

use App\Models\ExamSchedule;
use App\Models\Registration;
use App\Models\User;
use App\Policies\ExamSchedulePolicy;
use App\Policies\RegistrationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ExamSchedule::class, ExamSchedulePolicy::class);
        Gate::policy(Registration::class, RegistrationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
