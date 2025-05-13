<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Approver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Branch;
use App\Models\AVPFinance;
use App\Models\AVPFinanceStaff;
use Illuminate\Support\Arr;

class ApproverController extends Controller
{
    /**
     * Display a listing of the approvers.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStaff(Request $request)
    {
        try {
            $avpId = $request->query('id');

            // Fetch the ID for the 'HO' branch
            $HObranchID = (int) Branch::where('branch_code', 'HO')->value('id');

            // Fetch approvers from the HO branch, excluding the requester if they are an approver
            $HOapprovers = User::where('branch_code', $HObranchID)
                ->where('role', 'approver')
                ->where('id', '!=', $avpId)
                ->where('position', '!=', 'AVP - Finance')
                ->whereDoesntHave('approverStaffs')
                ->select('id', 'firstName', 'lastName', 'email', 'role', 'position', 'branch_code')
                ->get();


            return response()->json([
                'message' => 'Approvers retrieved successfully',
                'HOApprovers' => $HOapprovers,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving approvers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAVPFinanceStaffs()
    {
        // Retrieve all AVP Finance Staff entries with related user and staff details
        $AVPStaff = AVPFinanceStaff::with(['user', 'staff'])
            ->select('id', 'user_id', 'staff_id', 'branch_id')
            ->get();

        // Prepare the response array by accessing nested data and handling array of branch IDs
        $response = $AVPStaff->map(function ($staff) {
            // Decode branch_id to an array and ensure it's a flat array
            $branchIds = is_string($staff->branch_id) ? json_decode($staff->branch_id, true) : $staff->branch_id;

            // Ensure branchIds is an array and flatten it using Arr::flatten()
            $branchIds = is_array($branchIds) ? Arr::flatten($branchIds) : [$branchIds];

            $branchCodes = Branch::whereIn('id', $branchIds)
                ->pluck('branch_code')
                ->toArray();

            return [
                'id' => $staff->id,
                'user' => [
                    'id' => $staff->user->id,
                    'firstName' => $staff->user->firstName,
                    'lastName' => $staff->user->lastName,
                ],
                'staff' => [
                    'id' => $staff->staff->id,
                    'firstName' => $staff->staff->firstName,
                    'lastName' => $staff->staff->lastName,
                ],
                'branches' => $branchCodes, // Return the list of branch codes
            ];
        });

        return response()->json([
            'message' => 'AVP-Staffs retrieved successfully',
            'data' => $response,
        ]);
    }
    public function getAVPFinanceStaff($id)
    {
        // Retrieve the specific AVP Finance Staff entry with related user and staff details
        $AVPStaff = AVPFinanceStaff::with(['user', 'staff'])
            ->select('id', 'user_id', 'staff_id', 'branch_id')
            ->findOrFail($id);

        // Decode branch_id to an array and ensure it's a flat array
        $branchIds = is_string($AVPStaff->branch_id) ? json_decode($AVPStaff->branch_id, true) : $AVPStaff->branch_id;

        // Ensure branchIds is an array
        $branchIds = is_array($branchIds) ? $branchIds : [$branchIds];

        // Retrieve branch codes based on branch IDs

        // Prepare the response array
        $response = [
            'id' => $AVPStaff->id,
            'user' => [
                'firstName' => $AVPStaff->user->firstName,
                'lastName' => $AVPStaff->user->lastName,
            ],
            'staff' => [
                'firstName' => $AVPStaff->staff->firstName,
                'lastName' => $AVPStaff->staff->lastName,
            ],
            'branches' => $branchIds, // Return the list of branch codes
        ];

        return response()->json([
            'message' => 'AVP-Staff retrieved successfully',
            'data' => $response,
        ]);
    }
    public function updateAVPFinanceStaff(Request $request, $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'staff_id' => 'required|exists:a_v_p_finance_staff,staff_id',
            'branch_id' => 'required|array',
            'branch_id.*' => 'exists:branches,id',
        ]);

        DB::beginTransaction();

        try {
            $avpStaff = AVPFinanceStaff::findOrFail($id);

            $avpStaff->user_id = $validated['user_id'];
            $avpStaff->staff_id = $validated['staff_id'];
            $avpStaff->branch_id = $validated['branch_id'];

            $avpStaff->save();

            DB::commit();

            return response()->json([
                'message' => 'AVP Finance Staff updated successfully',
                'data' => $avpStaff,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while updating AVP Finance Staff',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAVPFinanceStaff($id)
    {
        try {

            $user = AVPFinanceStaff::findOrFail($id);
            $user->delete();

            return response()->json([
                'message' => 'AVP Staff deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('An error occurred while deleting the AVP Staff: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while deleting the AVP Staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAVP()
    {
        try {

            // Fetch the ID for the 'HO' branch
            $HObranchID = (int) Branch::where('branch_code', 'HO')->value('id');

            // Fetch approvers from the HO branch, excluding the requester if they are an approver
            $HOapprovers = User::where('branch_code', $HObranchID)
                ->where('role', 'approver')
                ->where('position', 'AVP - Finance')
                ->select('id', 'firstName', 'lastName', 'email', 'role', 'position', 'branch_code')
                ->get();


            return response()->json([
                'message' => 'Approvers retrieved successfully',
                'HOApprovers' => $HOapprovers,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving approvers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function index()
    {
        try {
            $approvers = User::with('approverStaffs')->where('role', 'approver')->doesntHave('approverStaffs')->get();
            return response()->json([
                'message' => 'Approvers retrieved successfully',
                'data' => $approvers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving approvers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getApprovers($userId)
    {

        // Return an error if an exception occurs
        try {


            // Fetch the ID for the 'HO' branch

            $HObranchID = Branch::where('branch_code', 'HO')->value('id');


            // Fetch approvers based on the branch ID

            $HOapprovers = User::with('approverStaffs')->where('branch_code', $HObranchID)
                ->whereDoesntHave('approverStaffs')

                ->where('role', 'approver')

                ->select('id', 'firstName', 'lastName', 'role', 'position', 'branch_code')

                ->get();

            // Find the requester by userId

            $requester = User::findOrFail($userId);

            $requesterBranch = (int) $requester->branch_code;


            $sameBranchApprovers = User::with('approverStaffs')->where('branch_code', $requesterBranch)
                ->whereDoesntHave('approverStaffs')

                ->where('role', 'approver')

                ->doesntHave('approverStaffs')

                ->where('position', '!=', 'Area Manager')

                ->select('id', 'firstName', 'lastName', 'role', 'position', 'branch_code')

                ->get();


            $areaManagerApprover = User::with('approverStaffs')->whereIn('id', function ($query) use ($requesterBranch) {

                $query->select('user_id')

                    ->from('area_managers')

                    ->whereJsonContains('branch_id', $requesterBranch);
            })

                ->whereDoesntHave('approverStaffs')

                ->get(['id', 'firstName', 'lastName', 'role', 'position', 'branch_code']);



            return response()->json([

                'message' => 'Approvers retrieved successfully',

                'HOApprovers' => $HOapprovers,

                'sameBranchApprovers' => $sameBranchApprovers,

                'areaManagerApprover' => $areaManagerApprover

            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([

                'message' => 'User not found',

                'error' => $e->getMessage(),

            ], 404);
        } catch (\Exception $e) {

            return response()->json([

                'message' => 'An error occurred while retrieving approvers',

                'error' => $e->getMessage(),

            ], 500);
        }
    }
    /**
     * Display the specified approver.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($userId)
    {
        try {
            $approver = Approver::where('user_id', $userId)->first();

            if (!$approver) {
                return response()->json([
                    'message' => 'Approver not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Approver retrieved successfully',
                'data' => $approver,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function createAVPFinance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'staff_id' => 'required|array',
            'staff_id.*' => 'required|exists:users,id',
        ]);

        $user = User::with('branch')->find($request->input('user_id'));

        if ($user->position !== 'AVP - Finance') {
            return response()->json([
                'message' => 'The selected user is not AVP - FINANCE.',
            ], 400);
        }

        AVPFinance::create([
            'user_id' => $request->input('user_id'),
            'staff_id' => $request->input('staff_id'),
        ]);


        return response()->json([
            'message' => 'AVP-Finance staff created successfully',
        ], 200);
    }

    public function createAVPFinanceStaff(Request $request)
    {

        // Validate the incoming request
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'staff_id' => 'required|exists:users,id',
            'branch_id' => 'required',
        ]);

        $user = User::with('branch')->find($request->input('user_id'));
        if ($user->branch !== 'Head Office') {
            return response()->json([
                'message' => 'The selected user is not from HEAD OFFICE.',
            ], 400);
        }

        // Check if the branch ID already exists in the JSON column of a_v_p_finance_staff
        $branchId = $request->input('branch_id');
        $exists = DB::table('a_v_p_finance_staff')
            ->whereJsonContains('branch_id', $branchId)
            ->exists();

        if ($exists) {
            $branchCodes = Branch::whereIn('id', $branchId)
                ->pluck('branch_code')
                ->toArray();

            return response()->json([
                'message' =>  implode(',', $branchCodes) . ' branch has already been taken.'
            ], 422);
        }

        // Create a new AVP-Finance Staff with assigned branches
        $avpFinanceStaff = AVPFinanceStaff::create([
            'user_id' => $request->input('user_id'),
            'staff_id' => $request->input('staff_id'),
            'branch_id' => $branchId,
        ]);

        return response()->json([
            'message' => 'AVP-Finance staff with assigned branches created successfully',
            'avp_staffs' => $avpFinanceStaff
        ], 201);
    }
    public function deleteApprover($user_id)
    {
        try {
            // Log the ID being looked for
            Log::info('Attempting to find approver with ID: ' . $user_id);


            // Update  the user's role to "user"
            $user = User::findOrFail($user_id);
            $user->role = 'User'; // Set the role to "user"
            $user->save();

            return response()->json([
                "status" => true,
                'message' => 'Approver deleted and user role updated successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('An error occurred while deleting the approver: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while deleting the approver',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
