import { useState, useEffect, useRef, useCallback, useSyncExternalStore } from "react";
import { getWorkoutPlan } from "@/services/workoutPlan";
import type { WorkoutPlan } from "@/types/workout";

export type GeneratingPlan = {
    id: number;
    status: "pending" | "processing" | "completed" | "failed";
};

const POLL_INTERVAL = 4000;
const STORAGE_KEY = "generating_plans";

// ── Shared in-memory store so every hook instance sees changes immediately ────
let memoryPlans: GeneratingPlan[] = loadFromStorage();
const listeners = new Set<() => void>();

function notify() {
    for (const l of listeners) l();
}

function loadFromStorage(): GeneratingPlan[] {
    try {
        const raw = sessionStorage.getItem(STORAGE_KEY);
        return raw ? (JSON.parse(raw) as GeneratingPlan[]) : [];
    } catch {
        return [];
    }
}

function persist(plans: GeneratingPlan[]): void {
    if (plans.length === 0) {
        sessionStorage.removeItem(STORAGE_KEY);
    } else {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(plans));
    }
}

function setMemoryPlans(next: GeneratingPlan[]): void {
    memoryPlans = next;
    persist(next);
    notify();
}

/** Call this from the generator hook right after dispatching a plan. */
export function trackGeneratingPlan(plan: WorkoutPlan): void {
    if (memoryPlans.some((p) => p.id === plan.id)) return;
    setMemoryPlans([...memoryPlans, { id: plan.id, status: plan.status }]);
}

/** Mark a plan as completed/failed from the generator's own polling. */
export function updateGeneratingPlanStatus(
    id: number,
    status: "completed" | "failed",
): void {
    const idx = memoryPlans.findIndex((p) => p.id === id);
    if (idx === -1) return;
    const next = [...memoryPlans];
    next[idx] = { ...next[idx], status };
    setMemoryPlans(next);
}

// ── Hook ──────────────────────────────────────────────────────────────────────

export function useGeneratingPlans() {
    const plans = useSyncExternalStore(
        (cb) => {
            listeners.add(cb);
            return () => listeners.delete(cb);
        },
        () => memoryPlans,
    );

    const pollingRef = useRef<ReturnType<typeof setInterval> | null>(null);

    const activePlans = plans.filter(
        (p) => p.status === "pending" || p.status === "processing",
    );
    const completedPlans = plans.filter((p) => p.status === "completed");
    const failedPlans = plans.filter((p) => p.status === "failed");

    const dismissPlan = useCallback((id: number) => {
        setMemoryPlans(memoryPlans.filter((p) => p.id !== id));
    }, []);

    const dismissAll = useCallback(() => {
        setMemoryPlans([]);
    }, []);

    // Poll only active plans
    useEffect(() => {
        if (activePlans.length === 0) {
            if (pollingRef.current) {
                clearInterval(pollingRef.current);
                pollingRef.current = null;
            }
            return;
        }

        const poll = async () => {
            let changed = false;
            const snapshot = memoryPlans;
            const updated = await Promise.all(
                snapshot.map(async (p) => {
                    if (p.status !== "pending" && p.status !== "processing") {
                        return p;
                    }
                    try {
                        const fresh = await getWorkoutPlan(p.id);
                        if (fresh.status !== p.status) changed = true;
                        return { id: p.id, status: fresh.status };
                    } catch {
                        return p;
                    }
                }),
            );

            if (changed) {
                setMemoryPlans(updated);
            }
        };

        poll();
        pollingRef.current = setInterval(poll, POLL_INTERVAL);

        return () => {
            if (pollingRef.current) {
                clearInterval(pollingRef.current);
                pollingRef.current = null;
            }
        };
    }, [activePlans.length]);

    return {
        activePlans,
        completedPlans,
        failedPlans,
        allPlans: plans,
        dismissPlan,
        dismissAll,
        isGenerating: activePlans.length > 0,
    };
}
