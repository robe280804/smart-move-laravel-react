import { useEffect, useRef, useState } from "react";

/**
 * Returns a ref and a boolean that turns true once the element
 * scrolls into the viewport. The observer is disconnected after
 * the first intersection so the animation only plays once.
 */
export function useScrollAnimation(threshold = 0.12) {
    const ref = useRef<HTMLDivElement>(null);
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const el = ref.current;
        if (!el) return;

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setIsVisible(true);
                    observer.unobserve(el);
                }
            },
            { threshold }
        );

        observer.observe(el);
        return () => observer.disconnect();
    }, [threshold]);

    return { ref, isVisible };
}
