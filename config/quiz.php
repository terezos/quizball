<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Answer Validation
    |--------------------------------------------------------------------------
    |
    | Enable AI-powered answer validation to accept misspellings, different
    | languages, and semantically equivalent answers.
    |
    */

    'ai_validation_enabled' => env('AI_VALIDATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Confidence Threshold
    |--------------------------------------------------------------------------
    |
    | Minimum confidence score (0-1) required for AI to accept an answer.
    | Higher values are stricter. Recommended: 0.80 - 0.90
    |
    */

    'ai_confidence_threshold' => env('AI_CONFIDENCE_THRESHOLD', 0.85),

    /*
    |--------------------------------------------------------------------------
    | AI Model
    |--------------------------------------------------------------------------
    |
    | OpenAI model to use for answer validation.
    | Options: gpt-4o-mini (fast, cheap), gpt-4o (more accurate)
    |
    */

    'ai_model' => env('AI_MODEL', 'gpt-4o-mini'),

];
