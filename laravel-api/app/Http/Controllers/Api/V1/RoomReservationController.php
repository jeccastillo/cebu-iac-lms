<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\RoomReservation;
use App\Models\ReservationEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class RoomReservationController extends Controller
{
    public function dashboard()
    {
        $today = date('Y-m-d');
        $todays_reservations = RoomReservation::with(['room', 'faculty'])
            ->where('dteReservationDate', $today)->get();
        $pending_reservations = RoomReservation::with(['room', 'faculty'])
            ->where('enumStatus', 'pending')->get();
        $my_reservations = RoomReservation::with(['room', 'faculty'])->get();

        // Add strRoomCode, strFirstname, strLastname to each reservation
        $mapReservation = function ($r) {
            $arr = $r->toArray();
            $arr['strRoomCode'] = $r->room->strRoomCode ?? null;
            $arr['strFirstname'] = $r->faculty->strFirstname ?? null;
            $arr['strLastname'] = $r->faculty->strLastname ?? null;
            return $arr;
        };

        return response()->json([
            'todays_reservations' => $todays_reservations->map($mapReservation),
            'pending_reservations' => $pending_reservations->map($mapReservation),
            'my_reservations' => $my_reservations->map($mapReservation)
        ]);
    }

    public function addForm()
    {
        $classrooms = DB::table('tb_mas_classrooms')->get();
        return response()->json(['classrooms' => $classrooms]);
    }

    public function show($id)
    {
        $item = RoomReservation::with(['room', 'faculty', 'approver', 'equipment.equipment'])->findOrFail($id);
        return response()->json($item);
    }

    public function editForm($id)
    {
        $item = RoomReservation::with(['room', 'faculty', 'equipment.equipment'])->findOrFail($id);
        $classrooms = DB::table('tb_mas_classrooms')->get();
        return response()->json(['item' => $item, 'classrooms' => $classrooms]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'intRoomID' => 'required|integer',
            'intFacultyID' => 'required|integer',
            'strPurpose' => 'required|string',
            'dteReservationDate' => 'required|date',
            'dteStartTime' => 'required|date_format:H:i:s',
            'dteEndTime' => 'required|date_format:H:i:s|after:dteStartTime',
            'intParentReservationID' => 'nullable|integer|exists:tb_mas_room_reservations,intReservationID',
            'equipment' => 'array',
            'equipment.*.intEquipmentID' => 'required|integer|exists:tb_mas_room_equipment,intEquipmentID',
            'equipment.*.intQuantityRequested' => 'required|integer|min:1',
        ]);
        $data['intCreatedBy'] = $request->header('X-User-ID') ?? $request->header('X-Faculty-ID');
        $data['enumStatus'] = 'pending';
        $reservation = DB::transaction(function () use ($data, $request) {
            $reservation = RoomReservation::create($data);
            if ($request->has('equipment')) {
                foreach ($request->input('equipment') as $eq) {
                    ReservationEquipment::create([
                        'intReservationID' => $reservation->intReservationID,
                        'intEquipmentID' => $eq['intEquipmentID'],
                        'intQuantityRequested' => $eq['intQuantityRequested'],
                        'enumStatus' => 'requested',
                    ]);
                }
            }
            return $reservation;
        });
        return response()->json($reservation->load(['creator', 'approver', 'parentReservation', 'equipment.equipment']), 201);
    }

    public function assignApprover(Request $request, $id)
    {
        $reservation = RoomReservation::findOrFail($id);
        $request->validate([
            'intApprovedBy' => 'required|integer|exists:tb_mas_faculty,intID',
        ]);
        if ($reservation->intCreatedBy == $request->intApprovedBy) {
            return response()->json(['message' => 'Creator cannot be approver'], 422);
        }
        $reservation->intApprovedBy = $request->intApprovedBy;
        $reservation->save();
        return response()->json($reservation->load(['creator', 'approver']));
    }

    public function approve(Request $request)
    {
        $id = $request->input('intReservationID');
        $reservation = RoomReservation::findOrFail($id);
        $facultyId = $request->header('X-Faculty-ID') ?? $request->header('X-User-ID');
        if ($reservation->enumStatus !== 'pending') {
            return response()->json(['message' => 'Reservation already processed'], 422);
        }
        $reservation->enumStatus = 'approved';
        $reservation->intApprovedBy = $facultyId;
        $reservation->dteApproved = now();
        $reservation->dteUpdated = now();
        if ($request->has('strRemarks')) {
            $reservation->strRemarks = $request->input('strRemarks');
        }
        $reservation->save();
        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['room', 'faculty', 'equipment.equipment'])]);
    }

    public function reject(Request $request)
    {
        $id = $request->input('intReservationID');
        $reservation = RoomReservation::findOrFail($id);
        $facultyId = $request->header('X-Faculty-ID') ?? $request->header('X-User-ID');
        if ($reservation->enumStatus !== 'pending') {
            return response()->json(['message' => 'Reservation already processed'], 422);
        }
        $reservation->enumStatus = 'rejected';
        $reservation->intApprovedBy = $facultyId;
        $reservation->dteApproved = now();
        $reservation->dteUpdated = now();
        if ($request->has('strRemarks')) {
            $reservation->strRemarks = $request->input('strRemarks');
        }
        $reservation->save();
        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['room', 'faculty', 'equipment.equipment'])]);
    }



    public function update(Request $request, $id)
    {
        $reservation = RoomReservation::findOrFail($id);
        $data = $request->all();
        $conflicts = $this->checkConflicts($data, $id);
        if ($conflicts->isNotEmpty()) {
            return response()->json(['error' => 'Room is not available at the selected time.', 'conflicts' => $conflicts], 409);
        }
        $data['dteUpdated'] = now();
        $reservation->update($data);
        return response()->json($reservation);
    }



    public function viewReservations()
    {
        $reservations = RoomReservation::all();
        return response()->json(['reservations' => $reservations]);
    }



    // Accept both POST (with id in body) and DELETE (with id in URL)
    public function destroy(Request $request, $id = null)
    {
        $reservationId = $id ?? $request->input('id');
        if (!$reservationId) {
            return response()->json(['message' => 'No reservation ID provided'], 400);
        }
        $reservation = RoomReservation::findOrFail($reservationId);
        $reservation->delete();
        return response()->json(['message' => 'success']);
    }

    public function checkAvailability(Request $request)
    {
        $data = $request->all();
        $conflicts = $this->checkConflicts($data, $data['exclude_id'] ?? null);
        return response()->json([
            'available' => empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }

    public function getAvailableRooms(Request $request)
    {
        $date = $request->input('date');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $rooms = $this->fetchAvailableRooms($date, $start_time, $end_time);
        return response()->json($rooms);
    }

    public function getScheduleData(Request $request)
    {
        $start_date = $request->input('start_date', date('Y-m-d'));
        $end_date = $request->input('end_date', date('Y-m-d', strtotime('+7 days')));
        $room_id = $request->input('room_id');
        $schedule_data = [];
        $reservations = $this->fetchReservationsByDateRange($start_date, $end_date, $room_id);
        foreach ($reservations as $reservation) {
            $schedule_data[] = [
                'id' => 'reservation_' . $reservation->intReservationID,
                'title' => 'Reserved: ' . $reservation->strPurpose,
                'start' => $reservation->dteReservationDate . 'T' . $reservation->dteStartTime,
                'end' => $reservation->dteReservationDate . 'T' . $reservation->dteEndTime,
                'backgroundColor' => '#f39c12',
                'borderColor' => '#e67e22',
                'type' => 'reservation',
                'room' => $reservation->intRoomID,
                'faculty' => $reservation->intFacultyID,
                'status' => $reservation->enumStatus
            ];
        }
        return response()->json($schedule_data);
    }

    // Removed user/role checks

    private function checkConflicts($data, $exclude_id = null)
    {
        $query = RoomReservation::where('intRoomID', $data['intRoomID'])
            ->where('dteReservationDate', $data['dteReservationDate'])
            ->where(function ($q) use ($data) {
                $q->where(function ($q2) use ($data) {
                    $q2->where('dteStartTime', '<=', $data['dteStartTime'])
                        ->where('dteEndTime', '>', $data['dteStartTime']);
                })
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('dteStartTime', '<', $data['dteEndTime'])
                            ->where('dteEndTime', '>=', $data['dteEndTime']);
                    })
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('dteStartTime', '>=', $data['dteStartTime'])
                            ->where('dteEndTime', '<=', $data['dteEndTime']);
                    });
            })
            ->whereIn('enumStatus', ['approved', 'pending']);
        if ($exclude_id) {
            $query->where('intReservationID', '!=', $exclude_id);
        }
        return $query->get();
    }

    private function fetchAvailableRooms($date, $start_time, $end_time)
    {
        $rooms = DB::table('tb_mas_classrooms')->get();
        $available = [];
        foreach ($rooms as $room) {
            $conflicts = RoomReservation::where('intRoomID', $room->intID)
                ->where('dteReservationDate', $date)
                ->where(function ($q) use ($start_time, $end_time) {
                    $q->where(function ($q2) use ($start_time) {
                        $q2->where('dteStartTime', '<=', $start_time)
                            ->where('dteEndTime', '>', $start_time);
                    })
                        ->orWhere(function ($q2) use ($end_time) {
                            $q2->where('dteStartTime', '<', $end_time)
                                ->where('dteEndTime', '>=', $end_time);
                        })
                        ->orWhere(function ($q2) use ($start_time, $end_time) {
                            $q2->where('dteStartTime', '>=', $start_time)
                                ->where('dteEndTime', '<=', $end_time);
                        });
                })
                ->whereIn('enumStatus', ['approved', 'pending'])
                ->count();
            if ($conflicts == 0) {
                $available[] = $room;
            }
        }
        return $available;
    }

    private function fetchReservationsByDateRange($start_date, $end_date, $room_id = null)
    {
        $query = RoomReservation::whereBetween('dteReservationDate', [$start_date, $end_date]);
        if ($room_id) {
            $query->where('intRoomID', $room_id);
        }
        return $query->get();
    }
}
