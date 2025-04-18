<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
        ]);

        $userId = $request->user()->id;
        $schedule = \App\Models\Schedule::findOrFail($validated['schedule_id']);
        if (!$schedule->is_available) {
            return response()->json(['message' => 'Schedule is not available'], 400);
        }

        $schedule->is_available = false;
        $schedule->save();

        $appointment = Appointment::create([
            'user_id' => $userId,
            'schedule_id' => $validated['schedule_id'],
            'appointment_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment
        ], 201);
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $appointments = Appointment::with(['user','schedule'])->get();
        } else {
            $appointments = Appointment::with(['user', 'schedule'])->where('user_id', $user->id)->get();
        }

        return response()->json($appointments);
    }

    public function show($id)
    {
        $appointment = Appointment::with(['user','schedule'])->findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'admin' && $appointment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized access to this appointment'], 403);
        }

        return response()->json($appointment);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
        ]);

        $newSchedule = \App\Models\Schedule::findOrFail($validated['schedule_id']);
        if (!$newSchedule->is_available) {
            return response()->json(['message' => 'Schedule is not available'], 400);
        }

        $oldSchedule = $appointment->schedule;
        $oldSchedule->is_available = true;
        $oldSchedule->save();

        $newSchedule->is_available = false;
        $newSchedule->save();

        $appointment->schedule_id = $validated['schedule_id'];
        $appointment->appointment_at = Carbon::now();
        $appointment->save();

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment
        ]);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $schedule = $appointment->schedule;
        $schedule->is_available = true;
        $schedule->save();

        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}
