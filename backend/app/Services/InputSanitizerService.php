<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

class InputSanitizerService
{
    /**
     * Prompt-injection and jailbreak patterns to strip from user input.
     * Covers: instruction overrides, role switching, system-prompt extraction,
     * template/model injection tokens, and named jailbreak techniques.
     *
     * @var array<int, string>
     */
    private const INJECTION_PATTERNS = [
        // Instruction override
        '/(ignore\s+(all\s+)?previous\s+instructions?)/ix',
        '/(forget\s+(all\s+)?previous\s+instructions?)/ix',
        '/(override\s+(your\s+)?instructions?)/ix',
        '/(disregard\s+(all\s+)?(previous|prior)\s+instructions?)/ix',
        '/(new\s+instructions?\s*(:|are))/ix',
        '/(from\s+now\s+on\s+(you|ignore|forget))/ix',

        // Role / identity switching
        '/(act\s+as\s+(a\s+)?(different|new|another|real)\s+\w+)/ix',
        '/(pretend\s+(you\s+)?(have\s+no\s+restrictions?|are\s+a|to\s+be))/ix',
        '/(you\s+are\s+now\s+a)/ix',
        '/(roleplay\s+as)/ix',

        // System-prompt extraction
        '/(reveal\s+(your\s+)?(prompt|instructions?|rules?|system\s+message|secrets?))/ix',
        '/(show\s+(me\s+)?(your\s+)?(prompt|instructions?|rules?|system\s+message))/ix',
        '/(repeat\s+(the\s+)?system\s+message)/ix',
        '/(print\s+(the\s+)?system\s+(message|prompt))/ix',
        '/(what\s+(are|were)\s+(your|you\s+told))/ix',
        '/(disclose\s+internal)/ix',

        // Template / model injection tokens
        '/(\{\{.+?\}\})/s',          // {{variable}} template injection
        '/(\[INST\])/i',             // Llama / Mistral token
        '/(<\|.+?\|>)/s',            // GPT special tokens <|endoftext|>
        '/(system\s*:\s*(new|you|ignore))/ix',

        // Named jailbreaks
        '/(\bDAN\s+mode\b)/i',
        '/(\bdeveloper\s+mode\b)/i',
        '/(\bjailbreak\b)/i',
        '/(\bprompt\s+injection\b)/i',
    ];

    /**
     * Quick block-list check used by the controller before dispatching the job.
     * Less thorough than sanitize() but O(1) for hot-path checks.
     *
     * @var array<int, string>
     */
    private array $blockedPhrases = [
        'system prompt',
        'your instructions',
        'show your prompt',
        'repeat the system message',
        'jailbreak',
        'DAN mode',
        'developer mode',
    ];

    public function isBlocked(string $value): bool
    {
        foreach ($this->blockedPhrases as $phrase) {
            if (stripos($value, $phrase) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitizes a text input:
     *   1. Strips HTML tags
     *   2. Removes null bytes and non-printable control characters
     *   3. Trims whitespace
     *   4. Truncates to maxLength
     *   5. Normalises newlines
     *   6. Removes all known prompt-injection patterns
     *
     * Logs a warning (without the raw value) when injection content is detected.
     */
    public function sanitize(string $value, int $maxLength = 500): string
    {
        $sanitized = strip_tags($value);
        $sanitized = str_replace("\0", '', $sanitized);
        // Strip non-printable control chars except \t and \n
        $sanitized = preg_replace('/[\x01-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sanitized) ?? '';
        $sanitized = trim($sanitized);
        $sanitized = mb_substr($sanitized, 0, $maxLength);
        $sanitized = preg_replace("/\r\n|\r|\n/", "\n", $sanitized) ?? '';

        $injectionFound = false;

        foreach (self::INJECTION_PATTERNS as $pattern) {
            $result = preg_replace($pattern, '', $sanitized);

            if ($result !== null && $result !== $sanitized) {
                $injectionFound = true;
                $sanitized = $result;
            }
        }

        if ($injectionFound) {
            Log::warning('Possible prompt injection attempt detected and stripped', [
                'context' => 'InputSanitizerService',
                'length' => mb_strlen($value),
            ]);
        }

        return trim($sanitized);
    }
}
