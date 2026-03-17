import { Document, Page, Text, View, StyleSheet } from "@react-pdf/renderer";
import type { WorkoutPlan } from "@/types/workout";
import { FITNESS_GOALS, WORKOUT_TYPES, EXPERIENCE_LEVELS, DAYS_OF_WEEK } from "@/constants/const";

const GOAL_LABEL = Object.fromEntries(FITNESS_GOALS.map((g) => [g.value, g.label]));
const WORKOUT_TYPE_LABEL = Object.fromEntries(WORKOUT_TYPES.map((w) => [w.value, w.label]));
const EXPERIENCE_LABEL = Object.fromEntries(
    EXPERIENCE_LEVELS.map((l) => [l, l.charAt(0).toUpperCase() + l.slice(1)]),
);

const getDayName = (day: number): string => DAYS_OF_WEEK[day - 1] ?? "Unknown";

const styles = StyleSheet.create({
    page: {
        fontFamily: "Helvetica",
        fontSize: 10,
        padding: 40,
        backgroundColor: "#ffffff",
        color: "#1e293b",
    },
    // ── Header ──────────────────────────────────────────────────────────────
    header: {
        marginBottom: 20,
        paddingBottom: 16,
        borderBottomWidth: 2,
        borderBottomColor: "#4f46e5",
    },
    headerLabel: {
        fontSize: 8,
        fontFamily: "Helvetica-Bold",
        color: "#6366f1",
        textTransform: "uppercase",
        letterSpacing: 1.5,
        marginBottom: 4,
    },
    headerTitle: {
        fontSize: 22,
        fontFamily: "Helvetica-Bold",
        color: "#0f172a",
        marginBottom: 6,
    },
    headerMeta: {
        flexDirection: "row",
        gap: 12,
    },
    headerMetaItem: {
        fontSize: 9,
        color: "#64748b",
        backgroundColor: "#f1f5f9",
        paddingHorizontal: 8,
        paddingVertical: 3,
        borderRadius: 4,
    },
    // ── Stats row ────────────────────────────────────────────────────────────
    statsRow: {
        flexDirection: "row",
        gap: 10,
        marginBottom: 20,
    },
    statBox: {
        flex: 1,
        backgroundColor: "#f8fafc",
        borderWidth: 1,
        borderColor: "#e2e8f0",
        borderRadius: 6,
        padding: 10,
        alignItems: "center",
    },
    statValue: {
        fontSize: 16,
        fontFamily: "Helvetica-Bold",
        color: "#4f46e5",
        marginBottom: 2,
    },
    statLabel: {
        fontSize: 8,
        color: "#94a3b8",
        textTransform: "uppercase",
        letterSpacing: 0.8,
    },
    // ── Day card ────────────────────────────────────────────────────────────
    dayCard: {
        marginBottom: 14,
        borderWidth: 1,
        borderColor: "#e2e8f0",
        borderRadius: 8,
        overflow: "hidden",
    },
    dayHeader: {
        backgroundColor: "#1e293b",
        paddingHorizontal: 14,
        paddingVertical: 8,
        flexDirection: "row",
        justifyContent: "space-between",
        alignItems: "center",
    },
    dayName: {
        fontSize: 11,
        fontFamily: "Helvetica-Bold",
        color: "#ffffff",
    },
    dayDuration: {
        fontSize: 8,
        color: "#94a3b8",
    },
    dayBody: {
        padding: 12,
    },
    // ── Block ───────────────────────────────────────────────────────────────
    block: {
        marginBottom: 10,
    },
    blockName: {
        fontSize: 9,
        fontFamily: "Helvetica-Bold",
        color: "#6366f1",
        textTransform: "uppercase",
        letterSpacing: 0.8,
        marginBottom: 6,
    },
    // ── Exercise table ───────────────────────────────────────────────────────
    tableHeader: {
        flexDirection: "row",
        backgroundColor: "#f1f5f9",
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 4,
        marginBottom: 2,
    },
    tableRow: {
        flexDirection: "row",
        paddingHorizontal: 8,
        paddingVertical: 5,
        borderBottomWidth: 1,
        borderBottomColor: "#f1f5f9",
    },
    tableRowAlt: {
        backgroundColor: "#fafafa",
    },
    colExercise: { flex: 3, fontSize: 9, color: "#1e293b" },
    colExerciseHeader: { flex: 3, fontSize: 8, fontFamily: "Helvetica-Bold", color: "#64748b" },
    colStat: { flex: 1, fontSize: 9, color: "#475569", textAlign: "center" },
    colStatHeader: { flex: 1, fontSize: 8, fontFamily: "Helvetica-Bold", color: "#64748b", textAlign: "center" },
    colNote: { flex: 2, fontSize: 8, color: "#94a3b8", textAlign: "right" },
    colNoteHeader: { flex: 2, fontSize: 8, fontFamily: "Helvetica-Bold", color: "#64748b", textAlign: "right" },
    colWeight: { flex: 1, fontSize: 9, color: "#475569", textAlign: "center" },
    colWeightHeader: { flex: 1, fontSize: 8, fontFamily: "Helvetica-Bold", color: "#64748b", textAlign: "center" },
    // ── Footer ───────────────────────────────────────────────────────────────
    footer: {
        position: "absolute",
        bottom: 24,
        left: 40,
        right: 40,
        flexDirection: "row",
        justifyContent: "space-between",
        borderTopWidth: 1,
        borderTopColor: "#e2e8f0",
        paddingTop: 8,
    },
    footerText: {
        fontSize: 8,
        color: "#94a3b8",
    },
});

interface Props {
    plan: WorkoutPlan;
}

export const WorkoutPlanPdf = ({ plan }: Props) => {
    const totalExercises = plan.plan_days.reduce(
        (acc, day) => acc + day.workout_blocks.reduce((a, b) => a + b.block_exercises.length, 0),
        0,
    );

    return (
        <Document
            title={`${GOAL_LABEL[plan.goal] ?? plan.goal} — Workout Plan`}
            author="SmartMove"
        >
            <Page size="A4" style={styles.page}>
                {/* Header */}
                <View style={styles.header}>
                    <Text style={styles.headerLabel}>SmartMove · Workout Plan</Text>
                    <Text style={styles.headerTitle}>{GOAL_LABEL[plan.goal] ?? plan.goal}</Text>
                    <View style={styles.headerMeta}>
                        <Text style={styles.headerMetaItem}>
                            {WORKOUT_TYPE_LABEL[plan.workout_type] ?? plan.workout_type}
                        </Text>
                        <Text style={styles.headerMetaItem}>
                            {EXPERIENCE_LABEL[plan.experience_level] ?? plan.experience_level}
                        </Text>
                    </View>
                </View>

                {/* Stats */}
                <View style={styles.statsRow}>
                    <View style={styles.statBox}>
                        <Text style={styles.statValue}>{plan.training_days_per_week}</Text>
                        <Text style={styles.statLabel}>Days / Week</Text>
                    </View>
                    <View style={styles.statBox}>
                        <Text style={styles.statValue}>{plan.plan_days.length}</Text>
                        <Text style={styles.statLabel}>Sessions</Text>
                    </View>
                    <View style={styles.statBox}>
                        <Text style={styles.statValue}>{totalExercises}</Text>
                        <Text style={styles.statLabel}>Exercises</Text>
                    </View>
                    <View style={styles.statBox}>
                        <Text style={styles.statValue}>
                            {plan.plan_days.map((d) => getDayName(d.day_of_week).slice(0, 3)).join(" · ")}
                        </Text>
                        <Text style={styles.statLabel}>Training Days</Text>
                    </View>
                </View>

                {/* Plan Days */}
                {plan.plan_days.map((day) => (
                    <View key={day.id} style={styles.dayCard}>
                        <View style={styles.dayHeader}>
                            <Text style={styles.dayName}>
                                {getDayName(day.day_of_week)}
                                {day.workout_name ? `  —  ${day.workout_name}` : ""}
                            </Text>
                            <Text style={styles.dayDuration}>{day.duration_minutes} min</Text>
                        </View>

                        <View style={styles.dayBody}>
                            {day.workout_blocks.map((block) => (
                                <View key={block.id} style={styles.block}>
                                    <Text style={styles.blockName}>{block.name}</Text>

                                    {/* Table header */}
                                    <View style={styles.tableHeader}>
                                        <Text style={styles.colExerciseHeader}>Exercise</Text>
                                        <Text style={styles.colStatHeader}>Sets</Text>
                                        <Text style={styles.colStatHeader}>Reps</Text>
                                        <Text style={styles.colWeightHeader}>Weight</Text>
                                        <Text style={styles.colStatHeader}>Rest</Text>
                                        <Text style={styles.colNoteHeader}>Muscle Group</Text>
                                    </View>

                                    {/* Table rows */}
                                    {block.block_exercises.map((ex, i) => (
                                        <View
                                            key={ex.id}
                                            style={[styles.tableRow, i % 2 === 1 ? styles.tableRowAlt : {}]}
                                        >
                                            <Text style={styles.colExercise}>
                                                {ex.exercise.name ?? "—"}
                                            </Text>
                                            <Text style={styles.colStat}>
                                                {ex.sets ?? "—"}
                                            </Text>
                                            <Text style={styles.colStat}>
                                                {ex.reps ?? (ex.duration_seconds ? `${ex.duration_seconds}s` : "—")}
                                            </Text>
                                            <Text style={styles.colWeight}>
                                                {ex.weight ? `${ex.weight} kg` : "—"}
                                            </Text>
                                            <Text style={styles.colStat}>
                                                {ex.rest_seconds ? `${ex.rest_seconds}s` : "—"}
                                            </Text>
                                            <Text style={styles.colNote}>
                                                {ex.exercise.muscle_group ?? "—"}
                                            </Text>
                                        </View>
                                    ))}
                                </View>
                            ))}
                        </View>
                    </View>
                ))}

                {/* Footer */}
                <View style={styles.footer} fixed>
                    <Text style={styles.footerText}>SmartMove · Generated Workout Plan</Text>
                    <Text
                        style={styles.footerText}
                        render={({ pageNumber, totalPages }) => `${pageNumber} / ${totalPages}`}
                    />
                </View>
            </Page>
        </Document>
    );
};
