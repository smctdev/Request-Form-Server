<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchHead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BranchHeadController extends Controller
{
    public function getBranchHeads()
    {
        try {

            $BranchHeads = BranchHead::with('user.branch')->get();

            return response()->json([
                'data'          => $BranchHeads->map(fn($branchHead) => [
                    "id"        => $branchHead->id,
                    "user"      => $branchHead->user,
                    "branch_id" => $branchHead->branch_id,
                    "user_id"   => $branchHead->user_id,
                    "branches"  => Branch::whereIn("id", $branchHead->branch_id)->get(['branch_code'])
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Branch Heads', 'details' => $e->getMessage()], 500);
        }
    }

    public function getAllBranchHeads()
    {
        $branchHeads = [
            'Branch Manager',
            'Branch Manager/Sales Manager',
            'Branch Supervisor',
            'Branch Supervisor/Sales Supervisor'
        ];
        try {

            $BranchHeads = User::orderBy('firstName', 'asc')
                ->whereIn('position', $branchHeads)
                ->whereDoesntHave('branchHead')
                ->get();

            return response()->json([
                'data'          => $BranchHeads->map(fn($branchHead) => [
                    "id"        => $branchHead->id,
                    "user"      => $branchHead,
                    "branch_id" => $branchHead->branch_code,
                    "user_id"   => $branchHead->id,
                ]),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch Branch Heads', 'details' => $e->getMessage()], 500);
        }
    }

    public function createBranchHead(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|array',
            'branch_id.*' => ['required', 'exists:branches,id', 'unique:branch_heads,branch_id'],
        ]);

        $user = User::find($request->input('user_id'));

        $branchHeads = [
            'Branch Manager',
            'Branch Manager/Sales Manager',
            'Branch Supervisor',
            'Branch Supervisor/Sales Supervisor'
        ];

        if (!in_array($user->position, $branchHeads)) {
            return response()->json([
                'message' => 'The selected user is not an Branch Head.',
            ], 400);
        }

        $notExistsBranchId = [];

        foreach ($request->branch_id as $branchId) {
            $exists = BranchHead::query()
                ->whereJsonContains('branch_id', $branchId)
                ->exists();

            if ($exists) {
                continue;
            } else {
                $notExistsBranchId[] = $branchId;
            }
        }


        if (collect($notExistsBranchId)->isEmpty()) {
            return response()->json([
                'message' => 'Branch selected already exists.',
            ], 400);
        }

        DB::transaction(function () use ($request, $user, $notExistsBranchId) {

            $branchHeadOldBranchId = BranchHead::where('user_id', $user->id)->first();

            BranchHead::updateOrCreate([
                'user_id' => $request->input('user_id')
            ], [
                'branch_id' => $branchHeadOldBranchId ? [...$branchHeadOldBranchId->branch_id, ...$notExistsBranchId] : $notExistsBranchId
            ]);

            $user->update([
                'role' => 'approver'
            ]);
        });

        return response()->json([
            'message' => 'Branch Head created successfully',
        ], 200);
    }



    //EDIT/UPDATE Branch Head
    public function updateBranchHead(Request $request, $userId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|array',
            'branch_id.*' => 'required|exists:branches,id',
        ]);

        // Find BranchHead by user_id
        $BranchHead = BranchHead::where('user_id', $userId)->first();

        if (!$BranchHead) {
            return response()->json([
                'message' => 'Branch Head not found or the selected user is not an Branch Head.',
            ], 404);
        }

        $notExistsBranchId = [];

        $user = User::find($request->input('user_id'));

        foreach ($request->branch_id as $branchId) {
            $exists = BranchHead::query()
                ->whereJsonContains('branch_id', $branchId)
                ->exists();

            if ($exists) {
                continue;
            } else {
                $notExistsBranchId[] = $branchId;
            }
        }

        $newBranchIds = [...array_diff($user->branchHead->branch_id, $request->removed_branch_id), ...$notExistsBranchId];

        if (collect($newBranchIds)->isEmpty()) {
            return response()->json([
                'message' => 'Branch selected already exists.',
            ], 400);
        }

        $branchHeads = [
            'Branch Manager',
            'Branch Manager/Sales Manager',
            'Branch Supervisor',
            'Branch Supervisor/Sales Supervisor'
        ];

        if (!in_array($user->position, $branchHeads)) {
            return response()->json([
                'message' => 'The selected user is not an Branch Head.',
            ], 400);
        }

        $BranchHead->update([
            'branch_id' => $newBranchIds,
        ]);

        return response()->json([
            'message' => 'Branch Head updated successfully',
        ], 200);
    }


    //VIEW Branch Head
    public function viewBranchHead($id)
    {
        try {

            $BranchHead = BranchHead::findOrFail($id);

            return response()->json([
                'message' => 'Branch Head retrieved successfully',
                'data' => $BranchHead
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving Branch Head',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //VIEW ALL Branch Head
    public function viewAllBranchHeads()
    {
        try {

            $BranchHead = BranchHead::select('id', 'user_id', 'branch_id')->with('user')->get();

            return response()->json([
                'message' => 'Branch Head retrieved successfully',
                'data' => $BranchHead,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving Branch Head',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //DELETE Branch Head
    public function deleteBranchHead($id)
    {
        try {

            $user = BranchHead::findOrFail($id);

            $user->delete();

            return response()->json([
                'message' => 'Branch Head deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the Branch Head',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
