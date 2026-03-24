import { Brain, Target, FileDown, Calendar, BarChart3, Users, ClipboardList, Cpu, Dumbbell, Trophy, Check, X } from "lucide-react";
import type { LucideIcon } from "lucide-react";

// ── Hero ─────────────────────────────────────────────────────────────────────

export const HERO_STATS = [
    { value: "10K+", label: "Active Users" },
    { value: "50K+", label: "Plans Created" },
    { value: "4.9★", label: "User Rating" },
] as const;

// ── Features ─────────────────────────────────────────────────────────────────

export type Feature = {
    icon: LucideIcon;
    title: string;
    description: string;
    gradient: string;
};

export const FEATURES: Feature[] = [
    {
        icon: Brain,
        title: "AI-Powered Intelligence",
        description: "Advanced algorithms analyze your fitness profile to create perfectly tailored workout plans.",
        gradient: "from-blue-500 to-cyan-500",
    },
    {
        icon: Target,
        title: "Goal-Oriented Plans",
        description: "Whether it's weight loss, muscle gain, or endurance, we customize plans for your specific goals.",
        gradient: "from-indigo-500 to-purple-500",
    },
    {
        icon: FileDown,
        title: "PDF Export",
        description: "Download your workout plan as a PDF and take it anywhere — gym, home, or outdoors.",
        gradient: "from-purple-500 to-pink-500",
    },
    {
        icon: Calendar,
        title: "Flexible Scheduling",
        description: "Fit workouts into your busy life with plans built around your available days and equipment.",
        gradient: "from-orange-500 to-red-500",
    },
    {
        icon: BarChart3,
        title: "Training Overview",
        description: "See your plans, sessions, and goal distribution at a glance from your personal dashboard.",
        gradient: "from-green-500 to-emerald-500",
    },
    {
        icon: Users,
        title: "Expert Backed",
        description: "Developed with certified trainers and sports scientists to ensure safe, effective results.",
        gradient: "from-teal-500 to-cyan-500",
    },
];

// ── How It Works ─────────────────────────────────────────────────────────────

export type Step = {
    number: string;
    icon: LucideIcon;
    title: string;
    description: string;
};

export const STEPS: Step[] = [
    {
        number: "01",
        icon: ClipboardList,
        title: "Tell Us About You",
        description: "Share your fitness level, goals, available equipment, and schedule preferences.",
    },
    {
        number: "02",
        icon: Cpu,
        title: "AI Creates Your Plan",
        description: "Our advanced AI analyzes your profile and generates a customized workout plan in seconds.",
    },
    {
        number: "03",
        icon: Dumbbell,
        title: "Start Training",
        description: "Access your personalized plan anytime and start working out at your own pace.",
    },
    {
        number: "04",
        icon: Trophy,
        title: "Achieve Your Goals",
        description: "Generate new plans whenever you need and keep building toward your fitness goals.",
    },
];

// ── Pricing ───────────────────────────────────────────────────────────────────

export type PricingFeature = {
    label: string;
    included: boolean;
    highlight?: string; // replaces boolean with a specific value like "3 total"
};

export type PlanKey = "free" | "advanced" | "pro";

export type PricingTier = {
    name: string;
    planKey: PlanKey;
    price: string;
    period: string;
    description: string;
    badge?: string;
    ctaLabel: string;
    highlighted: boolean;
    cardStyle: string;
    ctaStyle: string;
    features: PricingFeature[];
};

export const PRICING_TIERS: PricingTier[] = [
    {
        name: "Free",
        planKey: "free",
        price: "€0",
        period: "forever",
        description: "Perfect to discover the app and try what AI can do for your training.",
        ctaLabel: "Get Started Free",
        highlighted: false,
        cardStyle: "bg-white border border-slate-200",
        ctaStyle: "border border-slate-300 text-slate-700 hover:bg-slate-50",
        features: [
            { label: "Workout plan generations", included: true, highlight: "1 total" },
            { label: "Saved plans", included: true, highlight: "1 max" },
            { label: "PDF export", included: false },
            { label: "Exercise editing", included: false },
            { label: "Plan history", included: true, highlight: "Last 30 days" },
            { label: "Priority AI generation", included: false },
        ],
    },
    {
        name: "Advanced",
        planKey: "advanced",
        price: "€9.99",
        period: "per month",
        description: "For the serious athlete who trains consistently and wants full control.",
        badge: "Most Popular",
        ctaLabel: "Start Advanced",
        highlighted: true,
        cardStyle: "bg-gradient-to-b from-indigo-600 to-blue-700 border-0 text-white",
        ctaStyle: "bg-white text-indigo-600 hover:bg-indigo-50",
        features: [
            { label: "Workout plan generations", included: true, highlight: "5 / month" },
            { label: "Saved plans", included: true, highlight: "10 max" },
            { label: "PDF export", included: true },
            { label: "Exercise editing", included: true },
            { label: "Plan history", included: true, highlight: "Full history" },
            { label: "Priority AI generation", included: false },
        ],
    },
    {
        name: "Pro",
        planKey: "pro",
        price: "€19.99",
        period: "per month",
        description: "Unlimited power for coaches, enthusiasts, and people who never stop improving.",
        ctaLabel: "Go Pro",
        highlighted: false,
        cardStyle: "bg-slate-900 border border-slate-700 text-white",
        ctaStyle: "bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700",
        features: [
            { label: "Workout plan generations", included: true, highlight: "10 / month" },
            { label: "Saved plans", included: true, highlight: "Unlimited" },
            { label: "PDF export", included: true },
            { label: "Exercise editing", included: true },
            { label: "Plan history", included: true, highlight: "Full history" },
            { label: "Priority AI generation", included: true },
        ],
    },
];

// keep icon refs in the same file so PricingSection doesn't need to import lucide
export { Check, X };

// ── CTA ───────────────────────────────────────────────────────────────────────

export const CTA_BENEFITS = [
    "Personalized AI-generated workout plans",
    "Dashboard with training stats and goal analytics",
    "PDF export to take your plan anywhere",
    "Equipment-aware and schedule-flexible plans",
    "Plan history and workout management",
] as const;

// ── Footer ────────────────────────────────────────────────────────────────────

export const FOOTER_LINKS = {
    product: [
        { label: "Features", href: "#features" },
        { label: "Pricing", href: "#pricing" },
        { label: "How It Works", href: "#how-it-works" },
    ],
    company: [
        { label: "Contact", href: "mailto:hello@smartmoveai.com" },
    ],
    legal: [
        { label: "Privacy Policy", href: "/privacy" },
        { label: "Terms of Service", href: "/terms" },
    ],
} as const;
