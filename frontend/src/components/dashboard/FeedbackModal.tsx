import { useState } from "react";
import { Star, MessageSquarePlus } from "lucide-react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { feedbackSchema } from "@/components/forms/feedbackSchema";
import { storeFeedback } from "@/services/feedback";

export function FeedbackModal() {
    const [open, setOpen] = useState(false);
    const [rating, setRating] = useState<number | null>(null);
    const [hovered, setHovered] = useState<number | null>(null);
    const [message, setMessage] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<{ rating?: string; message?: string }>({});

    const resetForm = () => {
        setRating(null);
        setHovered(null);
        setMessage("");
        setErrors({});
    };

    const handleOpenChange = (value: boolean) => {
        setOpen(value);
        if (!value) {
            resetForm();
        }
    };

    const handleSubmit = async () => {
        const result = feedbackSchema.safeParse({
            rating,
            message: message.trim() || null,
        });

        if (!result.success) {
            const fieldErrors = result.error.flatten().fieldErrors;
            setErrors({
                rating: fieldErrors.rating?.[0],
                message: fieldErrors.message?.[0],
            });
            return;
        }

        setErrors({});
        setIsSubmitting(true);

        try {
            await storeFeedback({
                rating: result.data.rating,
                message: result.data.message,
            });

            toast.success("Thank you for your feedback!", {
                position: "top-center",
                duration: 5000,
                style: { background: "#22C55E", color: "#fff" },
            });

            handleOpenChange(false);
        } catch (error) {
            const err = error as { message?: string };
            toast.error(err.message ?? "Failed to send feedback.", {
                position: "top-center",
                duration: 5000,
                style: { background: "#FF4D4F", color: "#fff" },
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    const displayRating = hovered ?? rating;

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <button className="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-all w-full">
                    <MessageSquarePlus className="w-5 h-5" />
                    <span className="font-medium">Send Feedback</span>
                </button>
            </DialogTrigger>

            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="text-lg font-semibold">
                        Share your feedback
                    </DialogTitle>
                </DialogHeader>

                <div className="space-y-5 pt-2">
                    <div className="space-y-2">
                        <Label>Rating (optional)</Label>
                        <div className="flex gap-1">
                            {[1, 2, 3, 4, 5].map((star) => (
                                <button
                                    key={star}
                                    type="button"
                                    onClick={() =>
                                        setRating(rating === star ? null : star)
                                    }
                                    onMouseEnter={() => setHovered(star)}
                                    onMouseLeave={() => setHovered(null)}
                                    className="p-1 transition-transform hover:scale-110"
                                    aria-label={`Rate ${star} star${star > 1 ? "s" : ""}`}
                                >
                                    <Star
                                        className={`w-8 h-8 transition-colors ${
                                            displayRating !== null &&
                                            star <= displayRating
                                                ? "fill-amber-400 text-amber-400"
                                                : "text-slate-300"
                                        }`}
                                    />
                                </button>
                            ))}
                        </div>
                        {errors.rating && (
                            <p className="text-sm text-red-500">{errors.rating}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="feedback-message">
                            Message (optional)
                        </Label>
                        <Textarea
                            id="feedback-message"
                            placeholder="Tell us about your experience, a bug you found, or a feature you'd love to see..."
                            value={message}
                            onChange={(e) => setMessage(e.target.value)}
                            rows={4}
                            maxLength={1000}
                            className="resize-none"
                        />
                        <div className="flex justify-between items-center">
                            {errors.message ? (
                                <p className="text-sm text-red-500">
                                    {errors.message}
                                </p>
                            ) : (
                                <span />
                            )}
                            <span className="text-xs text-slate-400 ml-auto">
                                {message.length}/1000
                            </span>
                        </div>
                    </div>

                    <div className="flex gap-3 justify-end pt-1">
                        <Button
                            variant="outline"
                            onClick={() => handleOpenChange(false)}
                            disabled={isSubmitting}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={handleSubmit}
                            disabled={isSubmitting}
                            className="bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700"
                        >
                            {isSubmitting ? "Sending..." : "Send feedback"}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
