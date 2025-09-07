<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Classlist;
use App\Models\Classroom;
use App\Models\RoomSchedule;
use App\Models\SchoolYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    /**
     * Display a listing of room schedules.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get campus_id from header (required)
            $campusId = $request->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            $query = RoomSchedule::with([
                'classroom' => function ($query) {
                    $query->select('intID', 'strRoomCode', 'enumType', 'description');
                },
                'sy' => function ($query) {
                    $query->select('intID', 'strYearStart', 'strYearEnd', 'enumSem');
                },
                'classlist' => function ($query) {
                    $query->select('intID', 'strClassName', 'sectionCode', 'intFacultyID', 'intSubjectID')
                          ->with([
                              'faculty' => function ($q) {
                                  $q->select('intID', 'strFirstname', 'strLastname');
                              },
                              'subject' => function ($q) {
                                  $q->select('intID', 'strCode', 'strDescription');
                              }
                          ]);
                }
            ])->whereHas('sy', function ($syQuery) use ($campusId) {
                $syQuery->where('campus_id', $campusId);
            });

            // Filter by campus_id through school year
            $query->whereHas('sy', function ($syQuery) use ($campusId) {
                $syQuery->where('campus_id', $campusId);
            });

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('strScheduleCode', 'LIKE', "%{$search}%")
                    ->orWhereHas('classroom', function ($roomQuery) use ($search) {
                        $roomQuery->where('strRoomCode', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('classlist.subject', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('strCode', 'LIKE', "%{$search}%")
                                    ->orWhere('strDescription', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('classlist.faculty', function ($facultyQuery) use ($search) {
                        $facultyQuery->where('strFirstname', 'LIKE', "%{$search}%")
                                    ->orWhere('strLastname', 'LIKE', "%{$search}%");
                    });
                });
            }

            // Apply academic year filter
            if ($request->has('intSem') && !empty($request->intSem)) {
                $query->where('intSem', $request->intSem);
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

            // Apply classlist filter
            if ($request->has('classlist_id') && !empty($request->classlist_id)) {
                $query->where('intClasslistID', $request->classlist_id);
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
            // Get campus_id from header (required)
            $campusId = $request->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            // Get current user ID for encoder
            $encoderId = $request->header('X-Faculty-ID');
            if (!$encoderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty ID is required in X-Faculty-ID header'
                ], 400);
            }

            $validated = $request->validate([
                'intRoomID' => 'required|integer|exists:tb_mas_classrooms,intID',
                'intClasslistID' => 'required|integer|exists:tb_mas_classlist,intID',
                'strScheduleCode' => 'required|string|max:20',
                'strDay' => 'required|integer|min:1|max:7',
                'dteStart' => 'required|date_format:H:i',
                'dteEnd' => 'required|date_format:H:i|after:dteStart',
                'enumClassType' => 'required|in:lect,lab',
                'intSem' => 'required|integer|exists:tb_mas_sy,intID'
            ]);

            // Verify that the academic year belongs to the current campus
            $sy = SchoolYear::where('intID', $validated['intSem'])
                           ->where('campus_id', $campusId)
                           ->first();
            
            if (!$sy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Academic year does not belong to your campus'
                ], 422);
            }

            // Verify that the classlist belongs to the selected academic year
            $classlist = Classlist::where('intID', $validated['intClasslistID'])
                                 ->where('strAcademicYear', $validated['intSem'])
                                 ->first();
            
            if (!$classlist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Classlist does not belong to the selected academic year'
                ], 422);
            }

            // Get classlist details for conflict checking
            $classlistDetails = Classlist::with(['faculty'])->find($validated['intClasslistID']);
            
            // Check for comprehensive conflicts
            $scheduleConflicts = $this->checkScheduleConflict($validated);
            if (!empty($scheduleConflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule conflict detected. The room is already booked for this time slot.',
                    'conflicts' => $scheduleConflicts
                ], 422);
            }

            // Check for faculty conflicts
            if ($classlistDetails && $classlistDetails->intFacultyID) {
                $facultyConflicts = $this->checkFacultyConflict($validated, null, $classlistDetails->intFacultyID);
                if (!empty($facultyConflicts)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Faculty conflict detected. This faculty member already has a schedule for this time slot.',
                        'conflicts' => $facultyConflicts
                    ], 422);
                }
            }

            // Add encoder ID to the data
            $validated['intEncoderID'] = $encoderId;

            $schedule = RoomSchedule::create($validated);

            // Load the related data
            $schedule->load([
                'classroom',
                'sy',
                'classlist.faculty',
                'classlist.subject'
            ]);

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
            // Get campus_id from header (required)
            $campusId = request()->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            $schedule = RoomSchedule::with([
                'classroom' => function ($query) {
                    $query->select('intID', 'strRoomCode', 'enumType', 'description');
                },
                'sy' => function ($query) {
                    $query->select('intID', 'strYearStart', 'strYearEnd', 'enumSem');
                },
                'classlist' => function ($query) {
                    $query->select('intID', 'strClassName', 'sectionCode', 'intFacultyID', 'intSubjectID')
                          ->with([
                              'faculty' => function ($q) {
                                  $q->select('intID', 'strFirstname', 'strLastname');
                              },
                              'subject' => function ($q) {
                                  $q->select('intID', 'strCode', 'strDescription');
                              }
                          ]);
                }
            ])->whereHas('sy', function ($syQuery) use ($campusId) {
                $syQuery->where('campus_id', $campusId);
            })->findOrFail($id);

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
            // Get campus_id from header (required)
            $campusId = $request->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            // Get current user ID for encoder
            $encoderId = $request->header('X-Faculty-ID');
            if (!$encoderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty ID is required in X-Faculty-ID header'
                ], 400);
            }

            $schedule = RoomSchedule::whereHas('sy', function ($syQuery) use ($campusId) {
                $syQuery->where('campus_id', $campusId);
            })->findOrFail($id);

            $validated = $request->validate([
                'intRoomID' => 'required|integer|exists:tb_mas_classrooms,intID',
                'intClasslistID' => 'required|integer|exists:tb_mas_classlist,intID',
                'strScheduleCode' => 'required|string|max:20',
                'strDay' => 'required|integer|min:1|max:7',
                'dteStart' => 'required|date_format:H:i',
                'dteEnd' => 'required|date_format:H:i|after:dteStart',
                'enumClassType' => 'required|in:lect,lab',
                'intSem' => 'required|integer|exists:tb_mas_sy,intID'
            ]);

            // Verify that the academic year belongs to the current campus
            $sy = SchoolYear::where('intID', $validated['intSem'])
                           ->where('campus_id', $campusId)
                           ->first();
            
            if (!$sy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Academic year does not belong to your campus'
                ], 422);
            }

            // Verify that the classlist belongs to the selected academic year
            $classlist = Classlist::where('intID', $validated['intClasslistID'])
                                 ->where('strAcademicYear', $validated['intSem'])
                                 ->first();
            
            if (!$classlist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Classlist does not belong to the selected academic year'
                ], 422);
            }

            // Get classlist details for conflict checking
            $classlistDetails = Classlist::with(['faculty'])->find($validated['intClasslistID']);
            
            // Check for comprehensive conflicts (excluding current record)
            $scheduleConflicts = $this->checkScheduleConflict($validated, $id);
            if (!empty($scheduleConflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule conflict detected. The room is already booked for this time slot.',
                    'conflicts' => $scheduleConflicts
                ], 422);
            }

            // Check for faculty conflicts
            if ($classlistDetails && $classlistDetails->intFacultyID) {
                $facultyConflicts = $this->checkFacultyConflict($validated, $id, $classlistDetails->intFacultyID);
                if (!empty($facultyConflicts)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Faculty conflict detected. This faculty member already has a schedule for this time slot.',
                        'conflicts' => $facultyConflicts
                    ], 422);
                }
            }

            // Add encoder ID to the data
            $validated['intEncoderID'] = $encoderId;

            $schedule->update($validated);

            // Load the related data
            $schedule->load([
                'classroom',
                'sy',
                'classlist.faculty',
                'classlist.subject'
            ]);

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
            // Get campus_id from header (required)
            $campusId = request()->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            $schedule = RoomSchedule::whereHas('sy', function ($syQuery) use ($campusId) {
                $syQuery->where('campus_id', $campusId);
            })->findOrFail($id);
            
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
            // Get campus_id from header (required)
            $campusId = $request->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            $academicYear = $request->get('intSem');
            
            $query = RoomSchedule::whereHas('sy', function ($syQuery) use ($campusId) {
                $syQuery->where('campus_id', $campusId);
            });
            
            if ($academicYear) {
                $query->where('intSem', $academicYear);
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
                'unique_rooms_used' => (clone $query)->distinct('intRoomID')->count('intRoomID'),
                'unique_classlists_scheduled' => (clone $query)->distinct('intClasslistID')->count('intClasslistID')
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

    /**
     * Get available academic years for the campus.
     */
    public function getAcademicYears(Request $request): JsonResponse
    {
        try {
            // Get campus_id from header (required)
            $campusId = $request->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            $academicYears = SchoolYear::where('campus_id', $campusId)
                                     ->select('intID', 'strYearStart', 'strYearEnd', 'enumSem', 'term_student_type')
                                     ->orderBy('strYearStart', 'desc')
                                     ->orderBy('enumSem', 'asc')
                                     ->get();

            return response()->json([
                'success' => true,
                'data' => $academicYears,
                'message' => 'Academic years retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve academic years',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available classlists that don't have schedules yet.
     */
    public function getAvailableClasslists(Request $request): JsonResponse
    {
        try {
            // Get campus_id from header (required)
            $campusId = $request->header('X-Campus-ID');
            if (!$campusId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campus ID is required in X-Campus-ID header'
                ], 400);
            }

            $academicYear = $request->query('intSem');
            
            if (!$academicYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'Academic year (intSem) is required'
                ], 400);
            }

            // Verify that the academic year belongs to the current campus
            $sy = SchoolYear::where('intID', $academicYear)
                           ->where('campus_id', $campusId)
                           ->first();
            
            if (!$sy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Academic year does not belong to your campus'
                ], 422);
            }

            // Get classlists for the academic year that don't have schedules yet
            $classlists = Classlist::where('strAcademicYear', $academicYear)
                                  ->whereDoesntHave('schedules')
                                  ->with([
                                      'faculty' => function ($q) {
                                          $q->select('intID', 'strFirstname', 'strLastname');
                                      },
                                      'subject' => function ($q) {
                                          $q->select('intID', 'strCode', 'strDescription');
                                      }
                                  ])
                                  ->select('intID', 'strClassName', 'sectionCode', 'intFacultyID', 'intSubjectID')
                                  ->get();

            return response()->json([
                'success' => true,
                'data' => $classlists,
                'message' => 'Available classlists retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available classlists',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for schedule conflicts (room-based).
     */
    private function checkScheduleConflict($data, $excludeId = null)
    {
        if ($data['intRoomID'] == 99999) { // TBA room
            return [];
        }

        $query = RoomSchedule::with(['classlist.subject'])
            ->where(function ($q) use ($data) {
                $q->where(function ($timeQuery) use ($data) {
                    $timeQuery->whereBetween('dteStart', [$data['dteStart'], $data['dteEnd']])
                             ->orWhereBetween('dteEnd', [$data['dteStart'], $data['dteEnd']])
                             ->orWhere(function ($overlapQuery) use ($data) {
                                 $overlapQuery->where('dteStart', '<=', $data['dteStart'])
                                             ->where('dteEnd', '>=', $data['dteEnd']);
                             });
                });
            })
            ->where('intRoomID', '!=', 99999);

        if ($excludeId) {
            $query->where('intRoomSchedID', '!=', $excludeId);
        }

        if ($data['strDay'] == 7) {
            $query->where('intRoomID', $data['intRoomID'])
                  ->where('intSem', $data['intSem']);
        } else {
            $query->where('strDay', $data['strDay'])
                  ->where('intRoomID', $data['intRoomID'])
                  ->where('intSem', $data['intSem']);
        }

        return $query->get()->toArray();
    }

    /**
     * Check for faculty conflicts.
     */
    private function checkFacultyConflict($data, $excludeId = null, $facultyId = null)
    {
        $query = RoomSchedule::with(['classlist.subject'])
            ->where(function ($q) use ($data) {
                $q->where(function ($timeQuery) use ($data) {
                    $timeQuery->whereBetween('dteStart', [$data['dteStart'], $data['dteEnd']])
                             ->orWhereBetween('dteEnd', [$data['dteStart'], $data['dteEnd']])
                             ->orWhere(function ($overlapQuery) use ($data) {
                                 $overlapQuery->where('dteStart', '<=', $data['dteStart'])
                                             ->where('dteEnd', '>=', $data['dteEnd']);
                             });
                });
            });

        if ($excludeId) {
            $query->where('intRoomSchedID', '!=', $excludeId);
        }

        if ($data['strDay'] == 7) {
            $query->where('intRoomID', $data['intRoomID'])
                  ->where('intSem', $data['intSem']);
        } else {
            $query->where('strDay', $data['strDay'])
                  ->whereHas('classlist', function($q) use ($facultyId) {
                      $q->where('intFacultyID', $facultyId);
                  })
                  ->where('intSem', $data['intSem']);
        }

        return $query->get()->toArray();
    }
}
