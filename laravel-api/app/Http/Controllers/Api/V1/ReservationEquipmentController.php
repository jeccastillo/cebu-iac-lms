<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReservationEquipment;
use App\Models\RoomEquipment;
use App\Models\RoomReservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReservationEquipmentController extends Controller
{
    public function index(Request $request)
    {
        return ReservationEquipment::all();
    }
    public function show($id)
    {
        return ReservationEquipment::with('equipment')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'intReservationID' => 'required|exists:tb_mas_room_reservations,intReservationID',
            'intEquipmentID' => 'required|exists:tb_mas_room_equipment,intEquipmentID',
            'intQuantityRequested' => 'required|integer|min:1',
        ]);
        $equipment = RoomEquipment::findOrFail($data['intEquipmentID']);
        $reservedQty = ReservationEquipment::where('intEquipmentID', $equipment->intEquipmentID)
            ->whereIn('enumStatus', ['requested', 'approved', 'delivered'])
            ->sum('intQuantityRequested');
        if ($data['intQuantityRequested'] > ($equipment->intQuantityAvailable - $reservedQty)) {
            throw ValidationException::withMessages(['intQuantityRequested' => 'Not enough equipment available.']);
        }
        $reservationEquipment = ReservationEquipment::create([
            'intReservationID' => $data['intReservationID'],
            'intEquipmentID' => $data['intEquipmentID'],
            'intQuantityRequested' => $data['intQuantityRequested'],
            'enumStatus' => 'requested',
            'dteCreated' => now(),
        ]);
        return $reservationEquipment->load('equipment');
    }

    public function update(Request $request, $id)
    {
        $reservationEquipment = ReservationEquipment::findOrFail($id);
        if ($reservationEquipment->enumStatus !== 'requested') {
            abort(403);
        }
        $data = $request->validate([
            'intQuantityRequested' => 'required|integer|min:1',
        ]);
        $equipment = RoomEquipment::findOrFail($reservationEquipment->intEquipmentID);
        $reservedQty = ReservationEquipment::where('intEquipmentID', $equipment->intEquipmentID)
            ->whereIn('enumStatus', ['requested', 'approved', 'delivered'])
            ->where('intReservationEquipmentID', '!=', $id)
            ->sum('intQuantityRequested');
        if ($data['intQuantityRequested'] > ($equipment->intQuantityAvailable - $reservedQty)) {
            throw ValidationException::withMessages(['intQuantityRequested' => 'Not enough equipment available.']);
        }
        $reservationEquipment->intQuantityRequested = $data['intQuantityRequested'];
        $reservationEquipment->save();
        return $reservationEquipment->load('equipment');
    }

    public function destroy($id)
    {
        $reservationEquipment = ReservationEquipment::findOrFail($id);
        if ($reservationEquipment->enumStatus !== 'requested') {
            abort(403);
        }
        $reservationEquipment->delete();
        return response()->noContent();
    }

    public function changeStatus(Request $request, $id)
    {
        $reservationEquipment = ReservationEquipment::findOrFail($id);
        $data = $request->validate([
            'enumStatus' => 'required|in:approved,denied,delivered,returned',
        ]);
        $now = now();
        switch ($data['enumStatus']) {
            case 'approved':
                if ($reservationEquipment->enumStatus !== 'requested') abort(403);
                $reservationEquipment->enumStatus = 'approved';
                $reservationEquipment->dteUpdated = $now;
                break;
            case 'denied':
                if ($reservationEquipment->enumStatus !== 'requested') abort(403);
                $reservationEquipment->enumStatus = 'denied';
                $reservationEquipment->dteUpdated = $now;
                break;
            case 'delivered':
                if ($reservationEquipment->enumStatus !== 'approved') abort(403);
                $reservationEquipment->enumStatus = 'delivered';
                $reservationEquipment->dteUpdated = $now;
                break;
            case 'returned':
                if ($reservationEquipment->enumStatus !== 'delivered') abort(403);
                $reservationEquipment->enumStatus = 'returned';
                $reservationEquipment->dteUpdated = $now;
                break;
        }
        $reservationEquipment->save();
        return $reservationEquipment->load('equipment');
    }
}
