<?php

namespace App\Http\Controllers\API;

use App\Enums\Status;
use App\Events\RequestAccessEvent;
use App\Http\Controllers\Controller;
use App\Models\RequestAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RequestAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $per_page = request('per_page') ?: 10;
        $search = request('search');
        $allRequestAccess = RequestAccess::with('user')
            ->when($search, fn($query) => $query->where(fn($subQuery) => $subQuery->where('status', "LIKE", "%{$search}%")
                ->orWhere('request_access_type', "LIKE", "%{$search}%")
                ->orWhere('request_access_code', "LIKE", "%{$search}%")))
            ->orderBy("created_at", "desc")
            ->paginate($per_page);

        return response()->json($allRequestAccess, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'request_access_type'       => ['required', 'in:approver_access,admin_access'],
            'message'                   => ['required', 'min:5', 'max:255']
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        $hasPendingRequest = RequestAccess::where('user_id', Auth::id())
            ->where('status', Status::PENDING)
            ->latest()
            ->count();

        if ($hasPendingRequest >= 1) {
            return response()->json("You already have a pending request. Please wait for it to be processed.", 405);
        }

        do {
            $accessCode = "RA-" . Str::random(20);
        } while (RequestAccess::where('request_access_code', $accessCode)->first());

        $requestAccess = RequestAccess::create([
            'user_id'                   => Auth::id(),
            'request_access_code'       => Str::upper($accessCode),
            'request_access_type'       => $request->request_access_type,
            'message'                   => $request->message,
            'status'                    => Status::PENDING,
        ]);

        $admin = User::where('role', 'Admin')
            ->first();

        RequestAccessEvent::dispatch($admin, $requestAccess->id, false);

        return response()->json([
            'message'       => 'Request Access created successfully',
            'code'          => $requestAccess->request_access_code
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $requestAccess = RequestAccess::find($id);

        if (!$requestAccess) {
            return response()->json('Request Access not found', 403);
        }

        $requestAccess->update([
            'status'    => $request->status
        ]);

        $user = User::with(
            'branch',
            'notedBies.notedBy',
            'approvedBies.approvedBy',
            'requestAccess',
        )
            ->where('id', $requestAccess->user_id)
            ->first();

        if ($requestAccess->status === Status::APPROVED) {
            $user->update([
                'role'      => $requestAccess->request_access_type === 'admin_access' ? 'Admin' : 'approver'
            ]);
        }

        RequestAccessEvent::dispatch($user, $requestAccess->id, false);

        return response()->json('Request Access updated successfully', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $requestAccess = RequestAccess::find($id);

        if (!$requestAccess) {
            return response()->json('Request Access not found', 403);
        }

        $preventDelete = $requestAccess->status === Status::APPROVED || $requestAccess->status === Status::DECLINED;

        if ($preventDelete) {
            return response()->json('Request access has already been approved or declined and cannot be deleted. However, you may submit a new request access.', 403);
        }

        $admin = User::with(
            'branch',
            'notedBies.notedBy',
            'approvedBies.approvedBy',
            'requestAccess',
        )
            ->where('role', 'Admin')
            ->first();

        RequestAccessEvent::dispatch($admin, $requestAccess->id, true);

        $requestAccess->delete();

        return response()->json('Request Access deleted successfully', 204);
    }
}
