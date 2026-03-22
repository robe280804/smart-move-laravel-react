<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $goalLabel }} — Workout Plan</title>
    <style>
        /* ── Reset & Base ─────────────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #ffffff;
            line-height: 1.5;
        }

        /* ── Header ───────────────────────────────────────────────────────── */
        .header {
            padding: 0 0 16px 0;
            margin-bottom: 20px;
            border-bottom: 3px solid #4f46e5;
        }

        .header-brand {
            font-size: 8px;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 6px;
        }

        .header-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .header-badges {
            margin-top: 4px;
        }

        .badge {
            display: inline-block;
            font-size: 9px;
            color: #475569;
            background: #f1f5f9;
            padding: 3px 10px;
            border-radius: 4px;
            margin-right: 8px;
        }

        /* ── Stats Row ────────────────────────────────────────────────────── */
        .stats-table {
            width: 100%;
            margin-bottom: 24px;
            border-collapse: separate;
            border-spacing: 8px 0;
        }

        .stats-table td {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-align: center;
            padding: 12px 8px;
            width: 25%;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #4f46e5;
            display: block;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 8px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── Day Card ─────────────────────────────────────────────────────── */
        .day-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .day-header {
            background: #1e293b;
            padding: 10px 16px;
        }

        .day-header-table {
            width: 100%;
        }

        .day-name {
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
        }

        .day-duration {
            font-size: 9px;
            color: #94a3b8;
            text-align: right;
        }

        .day-body {
            padding: 14px 16px;
        }

        /* ── Block ────────────────────────────────────────────────────────── */
        .block {
            margin-bottom: 14px;
        }

        .block:last-child {
            margin-bottom: 0;
        }

        .block-name {
            font-size: 9px;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* ── Exercise Table ───────────────────────────────────────────────── */
        .exercise-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .exercise-table thead th {
            background: #f1f5f9;
            color: #64748b;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        .exercise-table thead th.center {
            text-align: center;
        }

        .exercise-table thead th.right {
            text-align: right;
        }

        .exercise-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .exercise-table tbody tr.alt {
            background: #fafbfc;
        }

        .exercise-table tbody td {
            padding: 7px 8px;
            color: #334155;
            vertical-align: middle;
        }

        .exercise-table tbody td.center {
            text-align: center;
        }

        .exercise-table tbody td.right {
            text-align: right;
        }

        .exercise-name {
            font-weight: 600;
            color: #1e293b;
        }

        .exercise-equipment {
            font-size: 7.5px;
            color: #94a3b8;
            display: block;
            margin-top: 1px;
        }

        .muscle-badge {
            display: inline-block;
            font-size: 7.5px;
            color: #6366f1;
            background: #eef2ff;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .text-muted {
            color: #cbd5e1;
        }

        /* ── Footer ───────────────────────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 40px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
        }

        .footer-table {
            width: 100%;
        }

        .footer-right {
            text-align: right;
        }

        /* ── Page Setup ───────────────────────────────────────────────────── */
        @page {
            margin: 40px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-brand">SmartMove &middot; Workout Plan</div>
        <div class="header-title">{{ $goalLabel }}</div>
        <div class="header-badges">
            <span class="badge">{{ $workoutTypeLabel }}</span>
            <span class="badge">{{ $experienceLabel }}</span>
        </div>
    </div>

    {{-- Stats --}}
    <table class="stats-table">
        <tr>
            <td>
                <span class="stat-value">{{ $plan->training_days_per_week }}</span>
                <span class="stat-label">Days / Week</span>
            </td>
            <td>
                <span class="stat-value">{{ $plan->planDays->count() }}</span>
                <span class="stat-label">Sessions</span>
            </td>
            <td>
                <span class="stat-value">{{ $totalExercises }}</span>
                <span class="stat-label">Exercises</span>
            </td>
            <td>
                <span class="stat-value" style="font-size: 11px;">{{ $trainingDays }}</span>
                <span class="stat-label">Training Days</span>
            </td>
        </tr>
    </table>

    {{-- Plan Days --}}
    @foreach ($plan->planDays->sortBy('day_of_week') as $day)
        <div class="day-card">
            <div class="day-header">
                <table class="day-header-table">
                    <tr>
                        <td class="day-name">
                            {{ $dayNames[$day->day_of_week] ?? 'Day '.$day->day_of_week }}
                            @if ($day->workout_name)
                                &nbsp;&mdash;&nbsp;{{ $day->workout_name }}
                            @endif
                        </td>
                        <td class="day-duration">{{ $day->duration_minutes }} min</td>
                    </tr>
                </table>
            </div>

            <div class="day-body">
                @foreach ($day->workoutBlocks->sortBy('order') as $block)
                    <div class="block">
                        <div class="block-name">{{ $block->name }}</div>

                        <table class="exercise-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Exercise</th>
                                    <th class="center" style="width: 10%;">Sets</th>
                                    <th class="center" style="width: 10%;">Reps</th>
                                    <th class="center" style="width: 12%;">Weight</th>
                                    <th class="center" style="width: 10%;">Rest</th>
                                    <th class="center" style="width: 8%;">RPE</th>
                                    <th class="right" style="width: 20%;">Muscle Group</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($block->blockExercises->sortBy('order') as $index => $ex)
                                    <tr class="{{ $index % 2 === 1 ? 'alt' : '' }}">
                                        <td>
                                            <span class="exercise-name">{{ $ex->exercise->name ?? '—' }}</span>
                                            @if ($ex->exercise->equipment)
                                                <span class="exercise-equipment">{{ $ex->exercise->equipment }}</span>
                                            @endif
                                        </td>
                                        <td class="center">{{ $ex->sets ?? '—' }}</td>
                                        <td class="center">
                                            @if ($ex->reps)
                                                {{ $ex->reps }}
                                            @elseif ($ex->duration_seconds)
                                                {{ $ex->duration_seconds }}s
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="center">
                                            @if ($ex->weight)
                                                {{ $ex->weight }} kg
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="center">
                                            @if ($ex->rest_seconds)
                                                {{ $ex->rest_seconds }}s
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="center">
                                            @if ($ex->rpe)
                                                {{ $ex->rpe }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="right">
                                            @if ($ex->exercise->muscle_group)
                                                <span class="muscle-badge">{{ $ex->exercise->muscle_group }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Footer --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>SmartMove &middot; Generated Workout Plan &middot; {{ $generatedDate }}</td>
                <td class="footer-right">smartmove.app</td>
            </tr>
        </table>
    </div>

</body>
</html>
