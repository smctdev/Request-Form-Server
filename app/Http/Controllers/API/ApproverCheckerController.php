<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApproverChecker;
use App\Http\Requests\UpdateApproverChecker;
use App\Http\Resources\ApproverCheckerResource;
use App\Models\ApproverChecker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApproverCheckerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');

        $approverCheckers = User::query()
            ->with('approverCheckers.checker')
            ->search($search)
            ->whereHas('approverCheckers')
            ->latest('created_at')
            ->paginate($perPage);

        return ApproverCheckerResource::collection($approverCheckers);
    }

    public function approverCheckers()
    {
        $approver = request('approver');

        $checker = request('checker');

        $approvers = User::query()
            ->whereDoesntHave('approverCheckers')
            ->orWhere('id', $approver)
            ->whereNot('id', Auth::id())
            ->orderBy('firstName')
            ->get(['id', 'firstName', 'lastName']);

        $checkers = User::query()
            ->whereDoesntHave('checkers')
            ->orWhere('id', $checker)
            ->whereNot('id', Auth::id())
            ->orderBy('firstName')
            ->get(['id', 'firstName', 'lastName']);

        return response()->json([
            'approvers' => $approvers,
            'checkers'  => $checkers
        ]);
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
    public function store(StoreApproverChecker $request)
    {
        $request->validated();

        $approverChecker = ApproverChecker::query()
            ->updateOrCreate([
                'user_id'           => $request->approver
            ], [
                'checker_id'        => $request->checker,
                'checker_category'  => $request->checker_category
            ]);

        $user = User::query()
            ->where('id', $approverChecker?->user_id)
            ->first();

        $userChecker = User::query()
            ->where('id', $approverChecker?->checker_id)
            ->first();

        $user->update(['role' => 'approver']);

        $userChecker?->update(['role' => 'approver']);

        return new ApproverCheckerResource($user);
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
    public function update(UpdateApproverChecker $request, string $id)
    {
        $request->validated();

        $approverChecker = ApproverChecker::query()
            ->updateOrCreate([
                'user_id'           => $request->approver
            ], [
                'checker_id'        => $request->checker,
                'checker_category'  => $request->checker_category
            ]);

        $user = User::query()
            ->where('id', $approverChecker?->user_id)
            ->first();

        $userChecker = User::query()
            ->where('id', $approverChecker?->checker_id)
            ->first();

        $user->update(['role' => 'approver']);

        $userChecker?->update(['role' => 'approver']);

        return new ApproverCheckerResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApproverChecker $approverChecker)
    {
        $user = User::query()
            ->where('id', $approverChecker?->user_id)
            ->first();

        $userChecker = User::query()
            ->where('id', $approverChecker?->checker_id)
            ->first();

        $user->update(['role' => 'User']);

        $userChecker?->update(['role' => 'User']);

        $approverChecker->delete();

        return response()->json([
            'message' => 'Approver checker deleted successfully.'
        ], 200);
    }
}
