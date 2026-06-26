<?php

namespace App\Providers;

use App\Events\RegistrationStatusChanged;
use App\Listeners\SendRegistrationNotification;
use App\Models\ExamSchedule;
use App\Models\Registration;
use App\Models\User;
use App\Policies\ExamSchedulePolicy;
use App\Policies\RegistrationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(ExamSchedule::class, ExamSchedulePolicy::class);
        Gate::policy(Registration::class, RegistrationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Event::listen(RegistrationStatusChanged::class, SendRegistrationNotification::class);
    }
}
