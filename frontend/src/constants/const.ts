export const EXPERIENCE_LEVELS = [
    "beginner",
    "intermediate",
    "advanced",
    "professional"
] as const;

export const GENDERS = ["male", "female"] as const;

export const DAYS_OF_WEEK = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
    "Sunday"
] as const;

export const FITNESS_GOALS = [
    { value: "weight_loss", label: "Weight Loss", icon: "🔥", description: "Burn fat and reduce body weight" },
    { value: "muscle_gain", label: "Muscle Gain", icon: "💪", description: "Build muscle mass and size" },
    { value: "strength_building", label: "Strength Building", icon: "⚡", description: "Increase overall strength" },
    { value: "endurance", label: "Endurance", icon: "🏃", description: "Improve cardiovascular fitness" },
    { value: "flexibility", label: "Flexibility", icon: "🧘", description: "Enhance mobility and flexibility" },
    { value: "general_fitness", label: "General Fitness", icon: "✨", description: "Overall health and wellness" },
    { value: "body_recomposition", label: "Body Recomposition", icon: "⚖️", description: "Lose fat while building muscle" },
    { value: "athletic_performance", label: "Athletic Performance", icon: "🏅", description: "Improve speed, agility, and power" },
    { value: "rehabilitation", label: "Injury Recovery", icon: "🩹", description: "Safe training focused on recovery and rehab" },
    { value: "posture_correction", label: "Posture Correction", icon: "🧍", description: "Strengthen muscles that improve posture" },
    { value: "functional_fitness", label: "Functional Fitness", icon: "🏋️", description: "Improve everyday movement and stability" }
] as const;

export const EQUIPMENT_OPTIONS = [
    "Dumbbells",
    "Barbells",
    "Resistance Bands",
    "Pull-up Bar",
    "Bench",
    "Kettlebells",
    "Cable Machine",
    "Cardio Equipment",
    "Bodyweight Only"
] as const;

export const WORKOUT_TYPES = [
    { value: "strength", label: "Strength Training", icon: "💪" },
    { value: "cardio", label: "Cardio", icon: "❤️" },
    { value: "mobility", label: "Mobility & Flexibility", icon: "🧘" },
    { value: "conditioning", label: "Conditioning", icon: "⚡" },
    { value: "hiit", label: "HIIT", icon: "🔥" },
    { value: "bodyweight", label: "Bodyweight / Calisthenics", icon: "🤸" },
    { value: "functional", label: "Functional Training", icon: "🏋️" },
    { value: "core", label: "Core Training", icon: "🪨" },
    { value: "recovery", label: "Recovery / Stretching", icon: "🧎" }
] as const;

export const GOAL_TO_WORKOUT_TYPES: Record<string, string[]> = {
    weight_loss: ["cardio", "hiit"],
    muscle_gain: ["strength"],
    strength_building: ["strength"],
    endurance: ["cardio", "conditioning"],
    flexibility: ["mobility"],
    general_fitness: ["strength", "cardio"],
    body_recomposition: ["strength", "hiit"],
    athletic_performance: ["conditioning", "functional"],
    rehabilitation: ["recovery", "mobility"],
    posture_correction: ["core", "mobility"],
    functional_fitness: ["functional", "bodyweight"],
};

export const WORKOUT_STEPS = [
    { label: "Goal",        title: "What's your fitness goal?",       description: "Select the primary goal you want to achieve" },
    { label: "Schedule",    title: "Plan your schedule",              description: "Set how often and how long you want to train" },
    { label: "Equipment",   title: "Available equipment",             description: "Select all the equipment you have access to" },
    { label: "Preferences", title: "Constraints & preferences",       description: "Injuries, sports, and anything else to personalize your plan" },
    { label: "Generate",    title: "Generating your plan",            description: "Building your personalized workout" },
] as const;

/** Goals where bodyweight-only equipment produces a plan different from the user's expectation. */
export const STRENGTH_FOCUSED_GOALS = [
    "strength_building",
    "muscle_gain",
    "body_recomposition",
] as const;

export type ExperienceLevel = typeof EXPERIENCE_LEVELS[number];
export type Gender = typeof GENDERS[number];
