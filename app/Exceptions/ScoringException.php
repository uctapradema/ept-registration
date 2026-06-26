<?php

namespace App\Exceptions;

use InvalidArgumentException;

class ScoringException extends InvalidArgumentException
{
    public static function scoreOutOfRange(string $component, int $min, int $max): self
    {
        return new self("Score {$component} must be between {$min} and {$max}.");
    }

    public static function notReadyForScoring(): self
    {
        return new self('Registration is not ready for scoring.');
    }
}
