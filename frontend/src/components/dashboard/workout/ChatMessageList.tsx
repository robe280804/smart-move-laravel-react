import { Sparkles, Loader2 } from "lucide-react";
import type { MessageType } from "@/types/workout";
import { useAuth } from "@/contexts/AuthContext";

interface ChatMessageListProps {
    messages: MessageType[];
    isGenerating: boolean;
}

export function ChatMessageList({ messages, isGenerating }: ChatMessageListProps) {
    const { user } = useAuth();
    const userInitial = user?.name?.charAt(0).toUpperCase() ?? "?";

    return (
        <div className="space-y-4 mb-6 flex-1 overflow-y-auto min-h-0 max-h-72">
            {messages.map((message) => (
                <div
                    key={message.id}
                    className={`flex gap-3 ${message.role === "user" ? "justify-end" : ""}`}
                >
                    {message.role === "assistant" && (
                        <div className="w-8 h-8 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <Sparkles className="w-4 h-4 text-white" />
                        </div>
                    )}
                    <div className={`max-w-[80%] p-4 rounded-2xl ${message.role === "user"
                        ? "bg-gradient-to-r from-blue-600 to-indigo-600 text-white"
                        : "bg-slate-100 text-slate-900"
                        }`}>
                        <p className="whitespace-pre-line">{message.content}</p>
                    </div>
                    {message.role === "user" && (
                        <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0 text-white font-semibold text-sm">
                            {userInitial}
                        </div>
                    )}
                </div>
            ))}
            {isGenerating && (
                <div className="flex gap-3">
                    <div className="w-8 h-8 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <Loader2 className="w-4 h-4 text-white animate-spin" />
                    </div>
                    <div className="max-w-[80%] p-4 rounded-2xl bg-slate-100">
                        <p className="text-slate-600">Generating your personalized workout plan...</p>
                    </div>
                </div>
            )}
        </div>
    );
}
