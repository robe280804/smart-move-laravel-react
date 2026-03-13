export const EXPERIENCE_LEVELS = [
    "beginner",
    "intermediate",
    "advanced",
    "professional"
] as const;

export const GENDERS = ["male", "female"] as const;

export type ExperienceLevel = typeof EXPERIENCE_LEVELS[number]
export type Gender = typeof GENDERS[number]