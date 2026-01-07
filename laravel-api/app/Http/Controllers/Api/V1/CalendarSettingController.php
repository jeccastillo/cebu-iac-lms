<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CalendarSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CalendarSettingController extends Controller
{
    public function index()
    {
        return CalendarSetting::all();
    }
    public function show($id)
    {
        return CalendarSetting::findOrFail($id);
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $setting = CalendarSetting::create($data);
        return response()->json($setting, 201);
    }
    public function update(Request $request, $id)
    {
        $setting = CalendarSetting::findOrFail($id);
        $setting->update($request->all());
        return response()->json($setting);
    }
    public function destroy($id)
    {
        $setting = CalendarSetting::findOrFail($id);
        $setting->delete();
        return response()->json(null, 204);
    }
}
