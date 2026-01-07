<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RoomEquipment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoomEquipmentController extends Controller
{
    public function index()
    {
        return RoomEquipment::all();
    }
    public function show($id)
    {
        return RoomEquipment::findOrFail($id);
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $equipment = RoomEquipment::create($data);
        return response()->json($equipment, 201);
    }
    public function update(Request $request, $id)
    {
        $equipment = RoomEquipment::findOrFail($id);
        $equipment->update($request->all());
        return response()->json($equipment);
    }
    public function destroy($id)
    {
        $equipment = RoomEquipment::findOrFail($id);
        $equipment->delete();
        return response()->json(null, 204);
    }
}
