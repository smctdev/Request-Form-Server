<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AreaManager;
use App\Models\User;

class AreaManagerController extends Controller
{

    //CREATE AREA MANAGER
    public function getAreaManagers()
    {
        try {

            $areaManagers = User::where('position', 'Area Manager')->get();

            return response()->json(['area_managers' => $areaManagers], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch area managers', 'details' => $e->getMessage()], 500);
        }
    }

    public function createAreaManager(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|array',
            'branch_id.*' => 'required|exists:branches,id',
        ]);

        $user = User::find($request->input('user_id'));

        if ($user->position !== 'Area Manager') {
            return response()->json([
                'message' => 'The selected user is not an Area Manager.',
            ], 400);
        }

        AreaManager::create([
            'user_id' => $request->input('user_id'),
            'branch_id' => $request->input('branch_id'),
        ]);


        return response()->json([
            'message' => 'Area Manager created successfully',
        ], 200);
    }



    //EDIT/UPDATE AREA MANAGER
    public function updateAreaManager(Request $request, $userId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|array',
            'branch_id.*' => 'required|exists:branches,id',
        ]);

        // Find AreaManager by user_id
        $areaManager = AreaManager::where('user_id', $userId)->first();

        if (!$areaManager) {
            return response()->json([
                'message' => 'Area Manager not found.',
            ], 404);
        }

        $user = User::find($request->input('user_id'));

        if ($user->position !== 'Area Manager') {
            return response()->json([
                'message' => 'The selected user is not an Area Manager.',
            ], 400);
        }

        $areaManager->update([
            'branch_id' => $request->input('branch_id'),
        ]);

        return response()->json([
            'message' => 'Area Manager updated successfully',
        ], 200);
    }


    //VIEW AREA MANAGER
    public function viewAreaManager($id)
    {
        try {

            $areaManager = AreaManager::findOrFail($id);

            return response()->json([
                'message' => 'Area Manager retrieved successfully',
                'data' => $areaManager
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving Area Manager',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //VIEW ALL AREA MANAGER
    public function viewAllAreaManagers()
    {
        try {

            $areaManager = AreaManager::select('id', 'user_id', 'branch_id')->with('user')->get();

            return response()->json([
                'message' => 'Area Manager retrieved successfully',
                'data' => $areaManager,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving Area Manager',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    //DELETE AREA MANAGER
    public function deleteAreaManager($id)
    {
        try {

            $user = AreaManager::findOrFail($id);

            $user->delete();

            return response()->json([
                'message' => 'Area Manager deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the Area Manager',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
