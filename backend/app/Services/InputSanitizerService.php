<?php

declare(strict_types=1);

namespace App\Services;

class InputSanitizerService
{
    /** @var array<int, string> */
    private array $blockedPatterns = [
        'system prompt',
        'your instructions',
        'show your prompt',
        'repeat the system message',
    ];

    public function isBlocked(string $value): bool
    {
        foreach ($this->blockedPatterns as $pattern) {
            if (stripos($value, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitizes a text input: strips HTML tags, removes null bytes, trims whitespace,
     * limits length, normalizes newlines, and removes prompt injection patterns.
     */
    public function sanitize(string $value, int $maxLength = 500): string
    {
        $sanitized = strip_tags($value);
        $sanitized = str_replace("\0", '', $sanitized);
        $sanitized = trim($sanitized);
        $sanitized = mb_substr($sanitized, 0, $maxLength);
        $sanitized = preg_replace("/\r\n|\r|\n/", "\n", $sanitized) ?? '';
        $sanitized = preg_replace(
            '/(ignore\s+previous\s+instructions|
                    forget\s+previous\s+instructions|
                    reveal\s+(your\s+)?prompt|
                    show\s+(your\s+)?instructions|
                    print\s+(the\s+)?system\s+message|
                    repeat\s+(the\s+)?system\s+message|
                    what\s+are\s+your\s+rules|
                    what\s+were\s+you\s+told|
                    disclose\s+internal)/ix',
            '',
            $sanitized
        ) ?? '';

        return $sanitized;
    }
}
