<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomApprovers;
use App\Models\Approver;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomApproversController extends Controller
{

//CREATE CUSTOM APPROVERS
public function createApprovers(Request $request)
{
    // Fetch user_ids of existing approvers from the database
   $approverUserIds = Approver::pluck('user_id')->toArray();

    // Define validation rules
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
        'name' => 'required|string',
        'noted_by' => 'required|array',
        'noted_by.*' => 'in:' . implode(',', $approverUserIds),
        'approved_by' => 'required|array',
        'approved_by.*' => 'in:' . implode(',', $approverUserIds),
    ]);

    // Handle validation errors
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
        ], 422);
    }

    // Extract data from the request
    $userId = $request->input('user_id');
    $name = $request->input('name');
    $notedBy = $request->input('noted_by');
    $approvedBy = $request->input('approved_by');

    // Convert arrays to JSON strings (if needed)
    $notedByJson = json_encode($notedBy);
    $approvedByJson = json_encode($approvedBy);

    // Create the CustomApprovers record
    $customApprovers = CustomApprovers::create([
        'user_id' => $userId,
        'name' => $name,
        'noted_by' => $notedByJson,
        'approved_by' => $approvedByJson,
    ]);

    // Return success response
    return response()->json([
        'message' => 'Approvers created successfully',
        'custom_approvers' => $customApprovers,
    ], 201);
}

//EDIT/UPDATE CUSTOM APPROVERS
    public function updateApprovers(Request $request, $id)
    {
        
        $approverUsers = User::where('role', 'approver')->pluck('id')->toArray();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'noted_by' => 'required|array',
            'noted_by.*' => 'in:' . implode(',', $approverUsers),
            'approved_by' => 'required|array',
            'approved_by.*' => 'in:' . implode(',', $approverUsers),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $custom_approvers = CustomApprovers::find($id);

        if (!$custom_approvers) {
            return response()->json(['message' => 'Custom approvers not found'], 404);
        }

        try {
            $notedBy = $request->input('noted_by');
            $approvedBy = $request->input('approved_by');
        
            // Convert arrays to JSON strings (if needed)
            $notedByJson = json_encode($notedBy);
            $approvedByJson = json_encode($approvedBy);
            $custom_approvers->update([
                'name' => $request->input('name'),
                'noted_by' => $notedByJson,
                'approved_by' => $approvedByJson,
            ]);

            return response()->json(['message' => 'Custom approvers updated successfully'], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to update custom approvers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

//VIEW ALL APPROVERS
public function viewAllCustomApprovers()
{
    try {
        $custom_approvers = CustomApprovers::select('id', 'noted_by', 'approved_by', 'name')->get();

        return response()->json([
            'message' => 'Custom approvers retrieved successfully',
            'data' => $custom_approvers
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while retrieving custom approvers',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function viewCustomApproversByUser($user_id)
{
    try {
        $customApprovers = CustomApprovers::select('id', 'name', 'user_id', 'noted_by', 'approved_by')
            ->where('user_id', $user_id)
            ->get();

        return response()->json([
            'message' => 'Custom approvers retrieved successfully',
            'data' => $customApprovers
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while retrieving custom approvers',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getCustomApproverById($id)
{
    try {
        $customApprover = CustomApprovers::findOrFail($id);

        return response()->json([
            'message' => 'Custom approver retrieved successfully',
            'data' => $customApprover
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Custom approver not found',
            'error' => $e->getMessage()
        ], 404);
    }
}

public function show($approversId = null)
{
    try {
        if ($approversId) {
            $customApprover = CustomApprovers::find($approversId);

            if (!$customApprover) {
                return response()->json([
                    'message' => 'Custom approver not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Custom approver retrieved successfully',
                'data' => $customApprover,
            ], 200);
        } else {
            $customApprovers = CustomApprovers::all();

            return response()->json([
                'message' => 'All custom approvers retrieved successfully',
                'data' => $customApprovers,
            ], 200);
        }
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while retrieving custom approvers',
            'error' => $e->getMessage(),
        ], 500);
    }
}




//DELETE CUSTOM APPROVERS
public function deleteCustomApprovers($id)
{
    try {
        
        $user = CustomApprovers::findOrFail($id);

        $user->delete();

        return response()->json([
            'message' => 'Custom approvers deleted successfully',
        ], 200);

    } catch (\Exception $e) {
        Log::error('An error occurred while deleting the custom approvers: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred while deleting the custom approvers',
            'error' => $e->getMessage()
        ], 500);
    }
}       
    

}