<?php

namespace App\Events;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistrationStatusChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Registration $registration,
        public readonly ?RegistrationStatus $oldStatus,
        public readonly RegistrationStatus $newStatus,
    ) {}
}
