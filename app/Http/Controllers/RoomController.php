<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:10|unique:rooms,room_number',
            'room_type_id' => 'required|exists:room_types,id',
            'status' => 'required|in:Available,Occupied,Under Maintenance,Dirty',
        ]);
        $room = Room::create($validated);
        return response()->json($room, 201);
    }

    public function update(Request $request, int $id)
    {
        $room = Room::findOrFail($id);
        $validated = $request->validate([
            'room_number' => 'sometimes|string|max:10|unique:rooms,room_number,' . $room->id,
            'room_type_id' => 'sometimes|exists:room_types,id',
            'status' => 'sometimes|in:Available,Occupied,Under Maintenance,Dirty',
        ]);
        $room->update($validated);
        return response()->json($room);
    }

    public function destroy(int $id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return response()->json(['message' => 'Room deleted']);
    }
}