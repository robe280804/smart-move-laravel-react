import { Brain, Target, Zap, Calendar, TrendingUp, Users, ClipboardList, Cpu, Dumbbell, Trophy, Check, X } from "lucide-react";
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
        icon: Zap,
        title: "Adaptive Workouts",
        description: "Plans that evolve with your progress, automatically adjusting intensity and exercises.",
        gradient: "from-purple-500 to-pink-500",
    },
    {
        icon: Calendar,
        title: "Flexible Scheduling",
        description: "Fit workouts into your busy life with plans that adapt to your available time.",
        gradient: "from-orange-500 to-red-500",
    },
    {
        icon: TrendingUp,
        title: "Progress Tracking",
        description: "Visualize your improvements with detailed analytics and milestone celebrations.",
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
        description: "Follow your personalized plan with guided exercises and progress tracking.",
    },
    {
        number: "04",
        icon: Trophy,
        title: "Achieve Your Goals",
        description: "Watch as your plan adapts to your progress and celebrates your milestones.",
    },
];

// ── Pricing ───────────────────────────────────────────────────────────────────

export type PricingFeature = {
    label: string;
    included: boolean;
    highlight?: string; // replaces boolean with a specific value like "3 total"
};

export type PricingTier = {
    name: string;
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
        price: "€0",
        period: "forever",
        description: "Perfect to discover the app and try what AI can do for your training.",
        ctaLabel: "Get Started Free",
        highlighted: false,
        cardStyle: "bg-white border border-slate-200",
        ctaStyle: "border border-slate-300 text-slate-700 hover:bg-slate-50",
        features: [
            { label: "Workout plan generations", included: true, highlight: "3 total" },
            { label: "Saved plans", included: true, highlight: "2 max" },
            { label: "PDF export", included: false },
            { label: "Exercise editing", included: false },
            { label: "Plan history", included: true, highlight: "Last 30 days" },
            { label: "Priority AI generation", included: false },
        ],
    },
    {
        name: "Advanced",
        price: "€9.99",
        period: "per month",
        description: "For the serious athlete who trains consistently and wants full control.",
        badge: "Most Popular",
        ctaLabel: "Start Advanced",
        highlighted: true,
        cardStyle: "bg-gradient-to-b from-indigo-600 to-blue-700 border-0 text-white",
        ctaStyle: "bg-white text-indigo-600 hover:bg-indigo-50",
        features: [
            { label: "Workout plan generations", included: true, highlight: "10 / month" },
            { label: "Saved plans", included: true, highlight: "10 max" },
            { label: "PDF export", included: true },
            { label: "Exercise editing", included: true },
            { label: "Plan history", included: true, highlight: "Full history" },
            { label: "Priority AI generation", included: false },
        ],
    },
    {
        name: "Pro",
        price: "€19.99",
        period: "per month",
        description: "Unlimited power for coaches, enthusiasts, and people who never stop improving.",
        ctaLabel: "Go Pro",
        highlighted: false,
        cardStyle: "bg-slate-900 border border-slate-700 text-white",
        ctaStyle: "bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700",
        features: [
            { label: "Workout plan generations", included: true, highlight: "Unlimited" },
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
    "Progress tracking and analytics",
    "Exercise library with detailed guides",
    "Adaptive difficulty adjustments",
    "Nutritional recommendations",
] as const;

// ── Footer ────────────────────────────────────────────────────────────────────

export const FOOTER_LINKS = {
    product: [
        { label: "Features", href: "#" },
        { label: "Pricing", href: "#" },
        { label: "How It Works", href: "#" },
        { label: "Success Stories", href: "#" },
    ],
    company: [
        { label: "About Us", href: "#" },
        { label: "Blog", href: "#" },
        { label: "Careers", href: "#" },
        { label: "Contact", href: "#" },
    ],
    support: [
        { label: "Help Center", href: "#" },
        { label: "Privacy Policy", href: "#" },
        { label: "Terms of Service", href: "#" },
        { label: "FAQ", href: "#" },
    ],
} as const;
