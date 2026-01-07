<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReservationVehicle;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class ReservationVehicleController extends Controller
{
    public function dashboard()
    {
        $today = date('Y-m-d');
        $todays_reservations = ReservationVehicle::with(['vehicle', 'faculty', 'driver'])
            ->where('dteReservationDate', $today)->get();
        $pending_reservations = ReservationVehicle::with(['vehicle', 'faculty', 'driver'])
            ->where('enumStatus', 'pending')->get();
        $my_reservations = ReservationVehicle::with(['vehicle', 'faculty', 'driver'])->get();

        return response()->json([
            'todays_reservations' => $todays_reservations,
            'pending_reservations' => $pending_reservations,
            'my_reservations' => $my_reservations
        ]);
    }

    public function index()
    {
        $reservations = ReservationVehicle::with(['vehicle', 'faculty', 'driver', 'approver'])->get();
        return response()->json($reservations);
    }

    public function show($id)
    {
        $reservation = ReservationVehicle::with(['vehicle', 'faculty', 'driver', 'approver', 'creator'])->findOrFail($id);
        return response()->json($reservation);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'intVehicleID' => 'required|integer|exists:tb_mas_vehicles,intVehicleID',
            'intFacultyID' => 'required|integer|exists:tb_mas_faculty,intID',
            'strPurpose' => 'required|string|max:255',
            'strDestination' => 'required|string|max:255',
            'dteReservationDate' => 'required|date',
            'dteStartTime' => 'required|date_format:H:i:s',
            'dteEndTime' => 'required|date_format:H:i:s|after:dteStartTime',
            'intDriverID' => 'nullable|integer|exists:tb_mas_faculty,intID',
            'strRemarks' => 'nullable|string'
        ]);

        $conflicts = $this->checkConflicts($data);
        if ($conflicts->isNotEmpty()) {
            return response()->json([
                'error' => 'Vehicle is not available at the selected time.',
                'conflicts' => $conflicts
            ], 409);
        }

        $data['intCreatedBy'] = $request->header('X-Faculty-ID') ?? $request->header('X-User-ID');
        $data['enumStatus'] = 'pending';
        $data['dteCreated'] = now();

        $reservation = ReservationVehicle::create($data);
        return response()->json($reservation->load(['vehicle', 'faculty', 'driver']), 201);
    }

    public function update(Request $request, $id)
    {
        $reservation = ReservationVehicle::findOrFail($id);
        
        $data = $request->validate([
            'intVehicleID' => 'sometimes|integer|exists:tb_mas_vehicles,intVehicleID',
            'intFacultyID' => 'sometimes|integer|exists:tb_mas_faculty,intID',
            'strPurpose' => 'sometimes|string|max:255',
            'strDestination' => 'sometimes|string|max:255',
            'dteReservationDate' => 'sometimes|date',
            'dteStartTime' => 'sometimes|date_format:H:i:s',
            'dteEndTime' => 'sometimes|date_format:H:i:s',
            'intDriverID' => 'nullable|integer|exists:tb_mas_faculty,intID',
            'strRemarks' => 'nullable|string'
        ]);

        if (isset($data['intVehicleID']) || isset($data['dteReservationDate']) || isset($data['dteStartTime']) || isset($data['dteEndTime'])) {
            $checkData = array_merge($reservation->toArray(), $data);
            $conflicts = $this->checkConflicts($checkData, $id);
            if ($conflicts->isNotEmpty()) {
                return response()->json([
                    'error' => 'Vehicle is not available at the selected time.',
                    'conflicts' => $conflicts
                ], 409);
            }
        }

        $data['dteUpdated'] = now();
        $reservation->update($data);
        
        return response()->json($reservation->load(['vehicle', 'faculty', 'driver']));
    }

    public function destroy($id)
    {
        $reservation = ReservationVehicle::findOrFail($id);
        $reservation->delete();
        return response()->json(['message' => 'success']);
    }

    public function approve(Request $request)
    {
        $id = $request->input('intReservationVehicleID');
        $reservation = ReservationVehicle::findOrFail($id);
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
        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['vehicle', 'faculty', 'driver'])]);
    }

    public function reject(Request $request)
    {
        $id = $request->input('intReservationVehicleID');
        $reservation = ReservationVehicle::findOrFail($id);
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
        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['vehicle', 'faculty', 'driver'])]);
    }

    public function startUse(Request $request, $id)
    {
        $reservation = ReservationVehicle::findOrFail($id);
        
        if ($reservation->enumStatus !== 'approved') {
            return response()->json(['message' => 'Reservation must be approved first'], 422);
        }

        $reservation->enumStatus = 'in_use';
        $reservation->dteUpdated = now();
        $reservation->save();

        Vehicle::where('intVehicleID', $reservation->intVehicleID)
            ->update(['enumStatus' => 'in_use', 'dteUpdated' => now()]);

        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['vehicle', 'faculty', 'driver'])]);
    }

    public function complete(Request $request, $id)
    {
        $reservation = ReservationVehicle::findOrFail($id);
        
        if ($reservation->enumStatus !== 'in_use') {
            return response()->json(['message' => 'Reservation must be in use'], 422);
        }

        $reservation->enumStatus = 'completed';
        $reservation->dteActualReturn = now();
        $reservation->dteUpdated = now();
        $reservation->save();

        Vehicle::where('intVehicleID', $reservation->intVehicleID)
            ->update(['enumStatus' => 'available', 'dteUpdated' => now()]);

        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['vehicle', 'faculty', 'driver'])]);
    }

    public function checkAvailability(Request $request)
    {
        $data = $request->all();
        $conflicts = $this->checkConflicts($data, $data['exclude_id'] ?? null);
        return response()->json([
            'available' => $conflicts->isEmpty(),
            'conflicts' => $conflicts
        ]);
    }

    private function checkConflicts($data, $exclude_id = null)
    {
        $query = ReservationVehicle::where('intVehicleID', $data['intVehicleID'])
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
            ->whereIn('enumStatus', ['approved', 'pending', 'in_use']);

        if ($exclude_id) {
            $query->where('intReservationVehicleID', '!=', $exclude_id);
        }

        return $query->get();
    }
}
