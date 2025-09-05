<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RoomSchedule;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    /**
     * Display a listing of room schedules.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RoomSchedule::with([
                'classroom' => function ($query) {
                    $query->select('intID', 'strRoomCode', 'enumType', 'description');
                }
            ]);

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('strScheduleCode', 'LIKE', "%{$search}%")
                      ->orWhereHas('classroom', function ($roomQuery) use ($search) {
                          $roomQuery->where('strRoomCode', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Apply semester filter
            if ($request->has('semester') && !empty($request->semester)) {
                $query->where('intSem', $request->semester);
            }

            // Apply room filter
            if ($request->has('room_id') && !empty($request->room_id)) {
                $query->where('intRoomID', $request->room_id);
            }

            // Apply day filter
            if ($request->has('day') && !empty($request->day)) {
                $query->where('strDay', $request->day);
            }

            // Apply class type filter
            if ($request->has('class_type') && !empty($request->class_type)) {
                $query->where('enumClassType', $request->class_type);
            }

            // Order by day, start time
            $query->orderBy('strDay')
                  ->orderBy('dteStart')
                  ->orderBy('strScheduleCode');

            $schedules = $query->get();

            return response()->json([
                'success' => true,
                'data' => $schedules,
                'message' => 'Schedules retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve schedules',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created schedule.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'intRoomID' => 'required|integer|exists:tb_mas_classrooms,intID',
                'strScheduleCode' => 'required|string|max:20',
                'strDay' => 'required|integer|min:1|max:7',
                'dteStart' => 'required|date_format:H:i',
                'dteEnd' => 'required|date_format:H:i|after:dteStart',
                'enumClassType' => 'required|in:lect,lab',
                'intSem' => 'required|integer|min:1'
            ]);

            // Check for schedule conflicts
            $conflict = RoomSchedule::where('intRoomID', $validated['intRoomID'])
                ->where('strDay', $validated['strDay'])
                ->where('intSem', $validated['intSem'])
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('dteStart', [$validated['dteStart'], $validated['dteEnd']])
                          ->orWhereBetween('dteEnd', [$validated['dteStart'], $validated['dteEnd']])
                          ->orWhere(function ($q) use ($validated) {
                              $q->where('dteStart', '<=', $validated['dteStart'])
                                ->where('dteEnd', '>=', $validated['dteEnd']);
                          });
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule conflict detected. The room is already booked for this time slot.'
                ], 422);
            }

            $schedule = RoomSchedule::create($validated);

            // Load the related classroom data
            $schedule->load('classroom');

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'newid' => $schedule->intRoomSchedID,
                'data' => $schedule
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified schedule.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $schedule = RoomSchedule::with([
                'classroom' => function ($query) {
                    $query->select('intID', 'strRoomCode', 'enumType', 'description');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => 'Schedule retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified schedule.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $schedule = RoomSchedule::findOrFail($id);

            $validated = $request->validate([
                'intRoomID' => 'required|integer|exists:tb_mas_classrooms,intID',
                'strScheduleCode' => 'required|string|max:20',
                'strDay' => 'required|integer|min:1|max:7',
                'dteStart' => 'required|date_format:H:i',
                'dteEnd' => 'required|date_format:H:i|after:dteStart',
                'enumClassType' => 'required|in:lect,lab',
                'intSem' => 'required|integer|min:1'
            ]);

            // Check for schedule conflicts (excluding current record)
            $conflict = RoomSchedule::where('intRoomID', $validated['intRoomID'])
                ->where('strDay', $validated['strDay'])
                ->where('intSem', $validated['intSem'])
                ->where('intRoomSchedID', '!=', $id)
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('dteStart', [$validated['dteStart'], $validated['dteEnd']])
                          ->orWhereBetween('dteEnd', [$validated['dteStart'], $validated['dteEnd']])
                          ->orWhere(function ($q) use ($validated) {
                              $q->where('dteStart', '<=', $validated['dteStart'])
                                ->where('dteEnd', '>=', $validated['dteEnd']);
                          });
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule conflict detected. The room is already booked for this time slot.'
                ], 422);
            }

            $schedule->update($validated);

            // Load the related classroom data
            $schedule->load('classroom');

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'data' => $schedule
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified schedule.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $schedule = RoomSchedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get schedule summary/statistics.
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $semester = $request->get('semester');
            
            $query = RoomSchedule::query();
            
            if ($semester) {
                $query->where('intSem', $semester);
            }

            $summary = [
                'total_schedules' => $query->count(),
                'lecture_schedules' => (clone $query)->where('enumClassType', 'lect')->count(),
                'lab_schedules' => (clone $query)->where('enumClassType', 'lab')->count(),
                'schedules_by_day' => (clone $query)
                    ->selectRaw('strDay, COUNT(*) as count')
                    ->groupBy('strDay')
                    ->orderBy('strDay')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $days = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        return [$days[$item->strDay] ?? 'Unknown' => $item->count];
                    }),
                'unique_rooms_used' => (clone $query)->distinct('intRoomID')->count('intRoomID')
            ];

            return response()->json([
                'success' => true,
                'data' => $summary,
                'message' => 'Schedule summary retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve schedule summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
