import { toast } from "sonner";

const POSITION = "top-center" as const;

export const notify = {
    success: (message: string) =>
        toast.success(message, { position: POSITION, duration: 4000 }),

    error: (message: string) =>
        toast.error(message, { position: POSITION, duration: 5000 }),

    warning: (message: string) =>
        toast.warning(message, { position: POSITION, duration: 4500 }),

    info: (message: string) =>
        toast.info(message, { position: POSITION, duration: 4000 }),
};
