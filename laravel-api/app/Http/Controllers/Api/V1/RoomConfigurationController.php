<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RoomConfiguration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RoomConfigurationController extends Controller
{
    public function index()
    {
        return RoomConfiguration::all();
    }
    public function show($id)
    {
        return RoomConfiguration::findOrFail($id);
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $config = RoomConfiguration::create($data);
        return response()->json($config, 201);
    }
    public function update(Request $request, $id)
    {
        $config = RoomConfiguration::findOrFail($id);
        $config->update($request->all());
        return response()->json($config);
    }
    public function destroy($id)
    {
        $config = RoomConfiguration::findOrFail($id);
        $config->delete();
        return response()->json(null, 204);
    }
}
