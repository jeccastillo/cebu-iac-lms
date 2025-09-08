<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * Get schedules for multiple classlist IDs within a specific term (syid).
     * Returns array grouped by classlist_id: [classlist_id => [schedule_rows...]]
     */
    public function getClasslistSchedulesForTerm(array $classlistIds, $syid): array
    {
        if (empty($classlistIds) || !$syid) {
            return [];
        }

        $schedules = DB::table('tb_mas_room_schedule as rs')
            ->leftJoin('tb_mas_classrooms as c', 'rs.intRoomID', '=', 'c.intID')
            ->whereIn('rs.intClasslistID', $classlistIds)
            ->where('rs.intSem', $syid)
            ->select(
                'rs.intClasslistID as classlist_id',
                'rs.strDay as day',
                'rs.dteStart as start_time',
                'rs.dteEnd as end_time',
                'c.strRoomCode as room_code'
            )
            ->orderBy('rs.intClasslistID')
            ->orderBy('rs.strDay')
            ->orderBy('rs.dteStart')
            ->get();

        // Group by classlist_id
        $grouped = [];
        foreach ($schedules as $schedule) {
            $classlistId = $schedule->classlist_id;
            if (!isset($grouped[$classlistId])) {
                $grouped[$classlistId] = [];
            }
            $grouped[$classlistId][] = [
                'day' => $schedule->day,
                'start' => $schedule->start_time,
                'end' => $schedule->end_time,
                'room_code' => $schedule->room_code,
            ];
        }

        return $grouped;
    }

    /**
     * Summarize schedule rows into formatted strings.
     * Returns array with keys: days, times, rooms, text
     */
    public function summarizeSchedules(array $scheduleRows): array
    {
        if (empty($scheduleRows)) {
            return [
                'days' => null,
                'times' => null,
                'rooms' => null,
                'text' => null,
            ];
        }

        // Extract and process days
        $dayMap = [
            '1' => 'M', 'Monday' => 'M', 'Mon' => 'M',
            '2' => 'T', 'Tuesday' => 'T', 'Tue' => 'T', 'Tues' => 'T',
            '3' => 'W', 'Wednesday' => 'W', 'Wed' => 'W',
            '4' => 'Th', 'Thursday' => 'Th', 'Thu' => 'Th', 'Thurs' => 'Th',
            '5' => 'F', 'Friday' => 'F', 'Fri' => 'F',
            '6' => 'Sa', 'Saturday' => 'Sa', 'Sat' => 'Sa',
            '7' => 'Su', 'Sunday' => 'Su', 'Sun' => 'Su',
        ];

        $dayOrder = ['M' => 1, 'T' => 2, 'W' => 3, 'Th' => 4, 'F' => 5, 'Sa' => 6, 'Su' => 7];

        $days = [];
        $timeRanges = [];
        $rooms = [];

        foreach ($scheduleRows as $row) {
            // Process day
            $dayRaw = $row['day'] ?? '';
            $dayShort = $dayMap[$dayRaw] ?? $dayRaw;
            if ($dayShort && !in_array($dayShort, $days)) {
                $days[] = $dayShort;
            }

            // Process time range
            $start = $row['start'] ?? '';
            $end = $row['end'] ?? '';
            if ($start && $end) {
                $timeRange = $this->formatTimeRange($start, $end);
                if ($timeRange && !in_array($timeRange, $timeRanges)) {
                    $timeRanges[] = $timeRange;
                }
            }

            // Process room
            $roomCode = $row['room_code'] ?? '';
            if ($roomCode && !in_array($roomCode, $rooms)) {
                $rooms[] = $roomCode;
            }
        }

        // Sort days by order
        usort($days, function ($a, $b) use ($dayOrder) {
            return ($dayOrder[$a] ?? 999) - ($dayOrder[$b] ?? 999);
        });

        // Sort time ranges
        sort($timeRanges);

        // Sort rooms
        sort($rooms);

        // Create formatted strings
        $daysStr = !empty($days) ? implode('', $days) : null;
        $timesStr = !empty($timeRanges) ? implode(', ', $timeRanges) : null;
        $roomsStr = !empty($rooms) ? implode(', ', $rooms) : null;

        // Create formatted text
        $text = $this->formatScheduleText($daysStr, $timesStr, $roomsStr);

        return [
            'days' => $daysStr,
            'times' => $timesStr,
            'rooms' => $roomsStr,
            'text' => $text,
        ];
    }

    /**
     * Format time range from start and end times.
     */
    private function formatTimeRange(string $start, string $end): ?string
    {
        try {
            // Handle various time formats
            $startTime = $this->normalizeTime($start);
            $endTime = $this->normalizeTime($end);

            if (!$startTime || !$endTime) {
                return null;
            }

            return $startTime . '-' . $endTime;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Normalize time to HH:MM format.
     */
    private function normalizeTime(string $time): ?string
    {
        if (!$time) {
            return null;
        }

        // Remove any extra whitespace
        $time = trim($time);

        // If already in HH:MM format, return as is
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            $parts = explode(':', $time);
            return sprintf('%02d:%02d', (int)$parts[0], (int)$parts[1]);
        }

        // Try to parse as timestamp or other formats
        try {
            $timestamp = strtotime($time);
            if ($timestamp !== false) {
                return date('H:i', $timestamp);
            }
        } catch (\Exception $e) {
            // Continue to other parsing attempts
        }

        // If it's just a number, assume it's hours
        if (is_numeric($time)) {
            $hours = (int)$time;
            return sprintf('%02d:00', $hours);
        }

        return null;
    }

    /**
     * Format the final schedule text.
     */
    private function formatScheduleText(?string $days, ?string $times, ?string $rooms): ?string
    {
        $parts = [];

        if ($days && $times) {
            $parts[] = $days . ' ' . $times;
        } elseif ($times) {
            $parts[] = $times;
        } elseif ($days) {
            $parts[] = $days;
        }

        if ($rooms) {
            $parts[] = $rooms;
        }

        if (empty($parts)) {
            return null;
        }

        return implode(' — ', $parts);
    }

    /**
     * Enrich student records with schedule information.
     * Modifies the records array in place by adding schedule fields.
     */
    public function enrichRecordsWithSchedules(array &$records, $syid = null): void
    {
        if (empty($records)) {
            return;
        }

        // Collect all classlist IDs and their corresponding syids
        $classlistsByTerm = [];
        
        foreach ($records as $record) {
            $classlistId = $record['classlist_id'] ?? null;
            $recordSyid = $syid ?? ($record['syid'] ?? null);
            
            if ($classlistId && $recordSyid) {
                if (!isset($classlistsByTerm[$recordSyid])) {
                    $classlistsByTerm[$recordSyid] = [];
                }
                if (!in_array($classlistId, $classlistsByTerm[$recordSyid])) {
                    $classlistsByTerm[$recordSyid][] = $classlistId;
                }
            }
        }

        // Fetch schedules for each term
        $allSchedules = [];
        foreach ($classlistsByTerm as $termSyid => $classlistIds) {
            $termSchedules = $this->getClasslistSchedulesForTerm($classlistIds, $termSyid);
            foreach ($termSchedules as $classlistId => $schedules) {
                $allSchedules[$classlistId] = $schedules;
            }
        }

        // Enrich each record with schedule data
        foreach ($records as &$record) {
            $classlistId = $record['classlist_id'] ?? null;
            
            if ($classlistId && isset($allSchedules[$classlistId])) {
                $summary = $this->summarizeSchedules($allSchedules[$classlistId]);
                $record['schedule_days'] = $summary['days'];
                $record['schedule_times'] = $summary['times'];
                $record['schedule_rooms'] = $summary['rooms'];
                $record['schedule_text'] = $summary['text'];
            } else {
                $record['schedule_days'] = null;
                $record['schedule_times'] = null;
                $record['schedule_rooms'] = null;
                $record['schedule_text'] = null;
            }
        }
    }

    /**
     * Enrich records grouped by terms (for records-by-term endpoint).
     */
    public function enrichTermsWithSchedules(array &$terms): void
    {
        foreach ($terms as &$term) {
            $syid = $term['syid'] ?? null;
            if (isset($term['records']) && is_array($term['records'])) {
                $this->enrichRecordsWithSchedules($term['records'], $syid);
            }
        }
    }
}
