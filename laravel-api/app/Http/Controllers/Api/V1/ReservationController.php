<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RoomReservation;
use App\Models\ReservationEquipment;
use App\Models\RoomEquipment;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controller;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'intRoomID' => 'required|integer',
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
        $data['enumStatus'] = 'PENDING';
        $reservation = DB::transaction(function () use ($data, $request) {
            $reservation = RoomReservation::create($data);
            if ($request->has('equipment')) {
                foreach ($request->input('equipment') as $eq) {
                    ReservationEquipment::create([
                        'intReservationID' => $reservation->intReservationID,
                        'intEquipmentID' => $eq['intEquipmentID'],
                        'intQuantityRequested' => $eq['intQuantityRequested'],
                        'enumStatus' => 'PENDING',
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

    public function approve(Request $request, $id)
    {
        $reservation = RoomReservation::findOrFail($id);
        if ($reservation->intApprovedBy !== ($request->header('X-User-ID') ?? $request->header('X-Faculty-ID'))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($reservation->intCreatedBy == ($request->header('X-User-ID') ?? $request->header('X-Faculty-ID'))) {
            return response()->json(['message' => 'Creator cannot approve own reservation'], 422);
        }
        if ($reservation->enumStatus !== 'PENDING') {
            return response()->json(['message' => 'Reservation already processed'], 422);
        }
        $reservation->enumStatus = 'APPROVED';
        $reservation->dteApproved = now();
        $reservation->save();
        return response()->json($reservation->load(['creator', 'approver', 'equipment.equipment']));
    }

    public function reject(Request $request, $id)
    {
        $reservation = RoomReservation::findOrFail($id);
        if ($reservation->intApprovedBy !== ($request->header('X-User-ID') ?? $request->header('X-Faculty-ID'))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($reservation->intCreatedBy == ($request->header('X-User-ID') ?? $request->header('X-Faculty-ID'))) {
            return response()->json(['message' => 'Creator cannot reject own reservation'], 422);
        }
        if ($reservation->enumStatus !== 'PENDING') {
            return response()->json(['message' => 'Reservation already processed'], 422);
        }
        $reservation->enumStatus = 'REJECTED';
        $reservation->dteApproved = now();
        $reservation->save();
        return response()->json($reservation->load(['creator', 'approver', 'equipment.equipment']));
    }

    public function show($id)
    {
        $reservation = RoomReservation::with([
            'creator',
            'approver',
            'parentReservation',
            'equipment.equipment',
        ])->findOrFail($id);
        return response()->json($reservation);
    }
}
