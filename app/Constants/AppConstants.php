<?php

namespace App\Constants;

final class AppConstants
{
    // Pagination
    public const DEFAULT_PAGE_SIZE = 10;
    public const DASHBOARD_RECENT_LIMIT = 5;

    // Quota
    public const QUOTA_LOW_THRESHOLD = 10;

    // Payment
    public const MIN_CANCEL_REASON_LENGTH = 10;
    public const MAX_CANCEL_REASON_LENGTH = 500;
    public const DEFAULT_PAYMENT_DEADLINE_HOURS = 24;

    // Unique Code
    public const DEFAULT_UNIQUE_CODE_MIN = 100;
    public const DEFAULT_UNIQUE_CODE_MAX = 999;

    // Scoring
    public const MAX_SCORE = 100;
    public const MIN_SCORE = 0;
    public const PASSING_SCORE = 450;

    // Display
    public const STATUS_HISTORY_NOTE_LIMIT = 30;

    private function __construct() {}
}
