<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShareUserRequest;
use App\Models\RequestForm;
use App\Services\ShareService;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function __construct(public ShareService $shareService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shared_requests = $this->shareService->getSharedRequests();

        return response()->json($shared_requests, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShareUserRequest $request)
    {
        $request->validated();

        $data = $this->shareService->storeSharedRequest($request);

        return response()->json([
            'message' => "Successfully shared request to {$data[1]} users",
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestForm $shared_request)
    {
        $data = $this->shareService->getAllRequestSharedUsers($shared_request);

        return response()->json([
            'message' => "Fetched successfully",
            'data'    => $data['shared_users'],
            'title'   => $data['request_title']
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestForm $shared_request, string $user_id)
    {
        //
    }

    public function destroyByRequestId(RequestForm $shared_request, string $user_id)
    {
        $this->shareService->delete($shared_request, $user_id);

        return response()->json([
            'message' => 'Successfully deleted',
        ], 200);
    }

    public function listsOfUsersToShareRequest()
    {
        $request_id = request('request_id', '');

        return response()->json([
            'message' => 'Lists of users to share request',
            'data' => $this->shareService->listsOfUsersToShareRequest($request_id)
        ], 200);
    }
}