<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $scheduleId)
    {
        $validated = $request->validate([
            'review' => 'required|string',
        ]);

        $userId = $request->user()->id;

        $appointment = Appointment::where('user_id', $userId)->where('schedule_id', $scheduleId)->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        if($appointment->review) {
            return response()->json(['message' => 'Review already exists'], 400);
        }

        $appointment->review = $validated['review'];
        $appointment->save();

        return response()->json([
            'message' => 'Review added successfully',
            'appointment_id' => $appointment->id,
            'review' => $appointment->review,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'review' => 'required|string',
        ]);

        $appointment->review = $validated['review'];
        $appointment->save();

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $appointment->review,
        ]);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (is_null($appointment->review)) {
            return response()->json(['message' => 'No review to delete'], 404);
        }

        $appointment->review = null;
        $appointment->save();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
