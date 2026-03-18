import type { ReactNode, CSSProperties } from "react";
import { useScrollAnimation } from "@/hooks/useScrollAnimation";

interface AnimatedSectionProps {
    children: ReactNode;
    className?: string;
    delay?: number;
    /** slide direction: "up" (default) | "left" | "right" | "none" */
    direction?: "up" | "left" | "right" | "none";
}

const directionMap = {
    up: "translate-y-8",
    left: "-translate-x-8",
    right: "translate-x-8",
    none: "",
};

export function AnimatedSection({
    children,
    className = "",
    delay = 0,
    direction = "up",
}: AnimatedSectionProps) {
    const { ref, isVisible } = useScrollAnimation();

    const style: CSSProperties = delay ? { transitionDelay: `${delay}ms` } : {};

    const hiddenClasses = `opacity-0 ${directionMap[direction]}`;
    const visibleClasses = "opacity-100 translate-y-0 translate-x-0";

    return (
        <div
            ref={ref}
            style={style}
            className={`transition-all duration-700 ease-out ${isVisible ? visibleClasses : hiddenClasses} ${className}`}
        >
            {children}
        </div>
    );
}
