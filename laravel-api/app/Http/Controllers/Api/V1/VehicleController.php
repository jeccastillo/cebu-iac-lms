<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('creator')->get();
        return response()->json($vehicles);
    }

    public function show($id)
    {
        $vehicle = Vehicle::with('creator', 'reservations')->findOrFail($id);
        return response()->json($vehicle);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'strPlateNumber' => 'required|string|max:20|unique:tb_mas_vehicles,strPlateNumber',
            'strVehicleName' => 'required|string|max:100',
            'strBrand' => 'required|string|max:50',
            'strModel' => 'required|string|max:50',
            'intYear' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'enumType' => 'required|in:sedan,suv,van,pickup,minivan,coaster,bus,motorcycle,other',
            'intCapacity' => 'required|integer|min:1|max:60',
            'enumTransmission' => 'required|in:manual,automatic',
            'enumFuelType' => 'required|in:gasoline,diesel,electric,hybrid',
            'strColor' => 'nullable|string|max:30',
            'enumStatus' => 'nullable|in:available,in_use,maintenance,retired',
            'strLocation' => 'nullable|string|max:100',
            'decCostPerDay' => 'nullable|numeric|min:0',
            'strNotes' => 'nullable|string'
        ]);

        $data['intCreatedBy'] = $request->header('X-Faculty-ID') ?? $request->header('X-User-ID');
        $data['dteCreated'] = now();
        
        $vehicle = Vehicle::create($data);
        return response()->json($vehicle->load('creator'), 201);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        
        $data = $request->validate([
            'strPlateNumber' => 'sometimes|string|max:20|unique:tb_mas_vehicles,strPlateNumber,' . $id . ',intVehicleID',
            'strVehicleName' => 'sometimes|string|max:100',
            'strBrand' => 'sometimes|string|max:50',
            'strModel' => 'sometimes|string|max:50',
            'intYear' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'enumType' => 'sometimes|in:sedan,suv,van,pickup,minivan,coaster,bus,motorcycle,other',
            'intCapacity' => 'sometimes|integer|min:1|max:60',
            'enumTransmission' => 'sometimes|in:manual,automatic',
            'enumFuelType' => 'sometimes|in:gasoline,diesel,electric,hybrid',
            'strColor' => 'nullable|string|max:30',
            'enumStatus' => 'sometimes|in:available,in_use,maintenance,retired',
            'strLocation' => 'nullable|string|max:100',
            'decCostPerDay' => 'nullable|numeric|min:0',
            'strNotes' => 'nullable|string'
        ]);

        $data['dteUpdated'] = now();
        $vehicle->update($data);
        
        return response()->json($vehicle->load('creator'));
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();
        return response()->json(['message' => 'success']);
    }

    public function available()
    {
        $vehicles = Vehicle::where('enumStatus', 'available')->with('creator')->get();
        return response()->json($vehicles);
    }
}
