<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\VehicleReservation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleReservationController extends Controller
{
    public function dashboard()
    {
        $today = date('Y-m-d');
        $todays_reservations = VehicleReservation::with(['vehicle', 'faculty'])
            ->where('dteReservationDate', $today)
            ->get();

        $pending_reservations = VehicleReservation::with(['vehicle', 'faculty'])
            ->where('enumStatus', 'pending')
            ->get();

        $my_reservations = VehicleReservation::with(['vehicle', 'faculty'])
            ->get();

        return response()->json([
            'todays_reservations' => $todays_reservations,
            'pending_reservations' => $pending_reservations,
            'my_reservations' => $my_reservations
        ]);
    }

    public function index()
    {
        $reservations = VehicleReservation::with(['vehicle', 'faculty', 'driver', 'approver'])
            ->orderBy('dteReservationDate', 'desc')
            ->get();
        return response()->json($reservations);
    }

    public function show($id)
    {
        $reservation = VehicleReservation::with(['vehicle', 'faculty', 'driver', 'approver'])
            ->findOrFail($id);
        return response()->json($reservation);
    }

    public function addForm()
    {
        $vehicles = Vehicle::where('enumStatus', 'available')->get();
        return response()->json(['vehicles' => $vehicles]);
    }

    public function editForm($id)
    {
        $reservation = VehicleReservation::with(['vehicle', 'faculty', 'driver'])
            ->findOrFail($id);
        $vehicles = Vehicle::where('enumStatus', 'available')
            ->orWhere('intVehicleID', $reservation->intVehicleID)
            ->get();
        return response()->json(['reservation' => $reservation, 'vehicles' => $vehicles]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'intVehicleID' => 'required|exists:tb_mas_vehicles,intVehicleID',
            'intFacultyID' => 'required|exists:tb_mas_faculty,intID',
            'strPurpose' => 'required|string|max:500',
            'strDestination' => 'nullable|string|max:255',
            'dteReservationDate' => 'required|date',
            'dteStartTime' => 'required',
            'dteEndTime' => 'required',
            'dteReturnDate' => 'nullable|date',
            'intDriverID' => 'nullable|exists:tb_mas_faculty,intID',
            'strDriverName' => 'nullable|string|max:255',
            'strDriverLicense' => 'nullable|string|max:100',
            'strContactNumber' => 'nullable|string|max:50',
            'intPassengerCount' => 'nullable|integer|min:1'
        ]);

        $conflicts = $this->checkConflicts($data);
        if ($conflicts->isNotEmpty()) {
            return response()->json([
                'error' => 'Vehicle is not available at the selected time.',
                'conflicts' => $conflicts
            ], 409);
        }

        $data['intCreatedBy'] = $request->header('X-Faculty-ID') ?? $request->header('X-User-ID') ?? 1;
        $data['dteCreated'] = now();
        $data['enumStatus'] = 'pending';

        $reservation = VehicleReservation::create($data);
        return response()->json($reservation->load(['vehicle', 'faculty', 'driver']), 201);
    }

    public function update(Request $request, $id)
    {
        $reservation = VehicleReservation::findOrFail($id);

        $data = $request->validate([
            'intVehicleID' => 'required|exists:tb_mas_vehicles,intVehicleID',
            'intFacultyID' => 'required|exists:tb_mas_faculty,intID',
            'strPurpose' => 'required|string|max:500',
            'strDestination' => 'nullable|string|max:255',
            'dteReservationDate' => 'required|date',
            'dteStartTime' => 'required',
            'dteEndTime' => 'required',
            'dteReturnDate' => 'nullable|date',
            'intDriverID' => 'nullable|exists:tb_mas_faculty,intID',
            'strDriverName' => 'nullable|string|max:255',
            'strDriverLicense' => 'nullable|string|max:100',
            'strContactNumber' => 'nullable|string|max:50',
            'intPassengerCount' => 'nullable|integer|min:1'
        ]);

        $conflicts = $this->checkConflicts($data, $id);
        if ($conflicts->isNotEmpty()) {
            return response()->json([
                'error' => 'Vehicle is not available at the selected time.',
                'conflicts' => $conflicts
            ], 409);
        }

        $data['dteUpdated'] = now();
        $reservation->update($data);

        return response()->json($reservation->load(['vehicle', 'faculty', 'driver']));
    }

    public function destroy(Request $request, $id = null)
    {
        $reservationId = $id ?? $request->input('id');
        if (!$reservationId) {
            return response()->json(['message' => 'No reservation ID provided'], 400);
        }

        $reservation = VehicleReservation::findOrFail($reservationId);
        $reservation->delete();
        return response()->json(['message' => 'success']);
    }

    public function approve(Request $request)
    {
        $id = $request->input('intVehicleReservationID');
        $reservation = VehicleReservation::findOrFail($id);
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
        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['vehicle', 'faculty'])]);
    }

    public function reject(Request $request)
    {
        $id = $request->input('intVehicleReservationID');
        $reservation = VehicleReservation::findOrFail($id);
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
        return response()->json(['message' => 'success', 'reservation' => $reservation->load(['vehicle', 'faculty'])]);
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

    public function getAvailableVehicles(Request $request)
    {
        $date = $request->input('date');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');

        $vehicles = Vehicle::where('enumStatus', 'available')->get();
        $available = [];

        foreach ($vehicles as $vehicle) {
            $conflicts = VehicleReservation::where('intVehicleID', $vehicle->intVehicleID)
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
                ->whereIn('enumStatus', ['approved', 'pending', 'ongoing'])
                ->count();

            if ($conflicts == 0) {
                $available[] = $vehicle;
            }
        }

        return response()->json($available);
    }

    private function checkConflicts($data, $exclude_id = null)
    {
        $query = VehicleReservation::where('intVehicleID', $data['intVehicleID'])
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
            ->whereIn('enumStatus', ['approved', 'pending', 'ongoing']);

        if ($exclude_id) {
            $query->where('intVehicleReservationID', '!=', $exclude_id);
        }

        return $query->get();
    }
}
