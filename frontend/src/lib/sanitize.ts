/**
 * Client-side input sanitization and prompt-injection detection.
 *
 * NOTE: This is a UX-level defence. The authoritative security boundary
 * is the backend (InputSanitizerService + AgentCallRequest validation).
 * Never rely solely on frontend sanitization.
 */

/** Character limits that mirror the backend AgentCallRequest validation. */
export const TEXT_MAX_LENGTHS = {
    injuries: 500,
    sports: 500,
    preferredExercises: 500,
    additionalNotes: 1000,
} as const;

/**
 * Known prompt-injection and jailbreak patterns.
 * Covers: instruction overrides, role-switching, template tokens,
 * model-specific injection tokens, and social-engineering phrasing.
 */
const SUSPICIOUS_PATTERNS: RegExp[] = [
    // Instruction override
    /ignore\s+(all\s+)?previous\s+instructions?/i,
    /forget\s+(all\s+)?previous\s+instructions?/i,
    /override\s+(your\s+)?instructions?/i,
    /disregard\s+(all\s+)?(previous|prior)\s+instructions?/i,
    /new\s+instructions?\s*(:|are)/i,
    /from\s+now\s+on\s+(you|ignore|forget)/i,

    // Role / identity switching
    /act\s+as\s+(a\s+)?(different|new|another|real)/i,
    /pretend\s+(you\s+)?(have\s+no\s+restrictions?|are\s+a|to\s+be)/i,
    /you\s+are\s+now\s+a/i,
    /roleplay\s+as/i,

    // System prompt extraction
    /reveal\s+(your\s+)?(prompt|instructions?|rules?|system\s+message|secrets?)/i,
    /show\s+(me\s+)?(your\s+)?(prompt|instructions?|rules?|system\s+message)/i,
    /repeat\s+(the\s+)?system\s+message/i,
    /print\s+(the\s+)?system\s+(message|prompt)/i,
    /what\s+(are|were)\s+(your|you\s+told)/i,
    /disclose\s+internal/i,

    // Template / model injection tokens
    /\{\{.+?\}\}/,        // {{variable}} template injection
    /\[INST\]/i,          // Llama / Mistral injection token
    /<\|.+?\|>/,          // GPT special tokens e.g. <|endoftext|>
    /system\s*:\s*(new|you|ignore)/i,

    // Named jailbreaks
    /\bDAN\s+mode\b/i,            // "Do Anything Now"
    /\bdeveloper\s+mode\b/i,
    /\bjailbreak\b/i,
    /\bprompt\s+injection\b/i,
];

/**
 * Strips invisible control characters (null bytes, non-printable chars)
 * and enforces the max length. Safe to run on every keystroke.
 *
 * Does NOT strip printable characters like < > { } so the user can
 * write naturally ("weight < 80kg", "[avoid] burpees").
 */
export function sanitizeTextInput(value: string, maxLength: number): string {
    return value
        .replace(/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/g, '') // control chars (keep \t \n)
        .slice(0, maxLength);
}

/**
 * Returns true if the string contains a known prompt-injection pattern.
 * Call this before submitting a form step — warn the user, do not silently strip.
 */
export function containsSuspiciousPattern(value: string): boolean {
    return SUSPICIOUS_PATTERNS.some((pattern) => pattern.test(value));
}

/**
 * Checks all free-text fields at once.
 * Returns the name of the first suspicious field, or null if all are clean.
 */
export function findSuspiciousField(
    fields: Partial<Record<string, string>>,
): string | null {
    for (const [field, value] of Object.entries(fields)) {
        if (value && containsSuspiciousPattern(value)) {
            return field;
        }
    }
    return null;
}
