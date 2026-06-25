<?php

namespace App\Services;

use App\Constants\AppConstants;
use App\Models\Registration;
use App\Models\User;

class ScoringService
{
    public function inputScores(
        Registration $registration,
        int $listeningScore,
        int $structureScore,
        int $readingScore,
        User $gradedBy
    ): Registration {
        $this->validateScores($listeningScore, $structureScore, $readingScore);

        $averageScore = $this->calculateAverage($listeningScore, $structureScore, $readingScore);

        $registration->update([
            'listening_score' => $listeningScore,
            'structure_score' => $structureScore,
            'reading_score' => $readingScore,
            'average_score' => $averageScore,
            'graded_by' => $gradedBy->id,
            'graded_at' => now(),
            'ready_for_scoring' => false,
        ]);

        return $registration->fresh();
    }

    public function calculateAverage(int $listening, int $structure, int $reading): float
    {
        return round(($listening + $structure + $reading) / 3, 2);
    }

    public function isPassingScore(float $averageScore): bool
    {
        return $averageScore >= AppConstants::PASSING_SCORE;
    }

    private function validateScores(int $listening, int $structure, int $reading): void
    {
        $scores = ['listening' => $listening, 'structure' => $structure, 'reading' => $reading];

        foreach ($scores as $component => $score) {
            if ($score < AppConstants::MIN_SCORE || $score > AppConstants::MAX_SCORE) {
                throw new \InvalidArgumentException(
                    "Score {$component} must be between " . AppConstants::MIN_SCORE . " and " . AppConstants::MAX_SCORE
                );
            }
        }
    }
}
