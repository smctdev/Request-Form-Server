<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::all();

        if (count($positions) == 0) {
            return response()->json([
                "message" => "No position found",
            ], 404);
        }

        return response()->json([
            "position"          =>              $positions
        ]);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'value'         =>          ['required', 'string', 'max:255', 'unique:positions,value'],
        ]);

        if ($validation->fails()) {
            return response()->json([
                "errors"        =>          $validation->errors()
            ], 400);
        }

        $position = Position::create([
            'value'         =>          $request->value,
            'label'         =>          $request->value
        ]);

        return response()->json([
            'message'       =>          'New position "' . $position->value . '" added successfully',
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json([
                "message"       =>      "Position not found",
            ], 404);
        }

        $validation = Validator::make(request()->all(), [
            'value'         =>          ['required', 'string', 'max:255', 'unique:positions,value,' . $position->id],
        ]);

        if ($validation->fails()) {
            return response()->json([
                "errors"        =>          $validation->errors()
            ], 400);
        }

        $position->update([
            'value'         =>          $request->value,
            'label'         =>          $request->value
        ]);

        return response()->json([
            'message'       =>          'Position "' . $position->value . '" updated successfully',
        ], 200);
    }

    public function destroy($id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json([
                "message"       =>      "Position not found",
            ], 404);
        }

        $position->delete();

        return response()->json([
            'message'       =>          'Position "' . $position->value . '" deleted successfully',
        ], 200);
    }
}
