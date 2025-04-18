<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:schedules,date',
            'is_available' => 'nullable|boolean',
        ]);

        $schedule = Schedule::create([
            'date' => $validated['date'],
            "is_available" => $validated['is_available'] ?? true,
        ]);

        return response()->json([
            'message' => 'Schedule created successfully',
            'schedule' => $schedule
        ], 201);
    }

    public function index()
    {
        $schedules = Schedule::all();
        return response()->json($schedules);
    }

    public function show($id)
    {
        $schedule = Schedule::findOrFail($id);
        return response()->json($schedule);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $validated = $request->validate([
            'date' => 'sometimes|required|date|unique:schedules,date,' . $schedule->id,
            'is_available' => 'nullable|boolean',
        ]);

        $schedule->date = $validated['date'] ?? $schedule->date;
        if (isset($validated['is_available'])) {
            $schedule->is_available = $validated['is_available'];
        }

        $schedule->save();

        return response()->json([
            'message' => 'Schedule updated successfully',
            'schedule' => $schedule
        ]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully'
        ]);
    }
}
