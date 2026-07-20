<?php

namespace App\Services;

use App\Models\RequestForm;
use App\Models\User;
use App\Notifications\SharedRequestNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ShareService
{
    public function getSharedRequests()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');
        $status = request('status', '');
        $date_to = request('date_to', '') ? Carbon::parse(request('date_to', ''))->endOfDay() : '';
        $date_from = request('date_from', '') ? Carbon::parse(request('date_from', ''))->startOfDay() : '';

        $requests =  RequestForm::query()
            ->with(['user.branchCode', 'branchCode', 'branchCode', 'approvalProcess.user'])
            ->when(
                $search,
                fn($query)
                =>
                $query->where(
                    fn($subQuery)
                    =>
                    $subQuery->where('form_type', 'LIKE', "%{$search}%")
                        ->orWhere('status', 'LIKE', "%{$search}%")
                        ->orWhere('request_code', 'LIKE', "%{$search}%")
                        ->orWhere('completed_code', 'LIKE', "%{$search}%")
                        ->orWhereHas(
                            'branchCode',
                            fn($branch)
                            =>
                            $branch->where('branch_code', 'LIKE', "%{$search}%")
                                ->orWhere('branch_name', 'LIKE', "%{$search}%")
                                ->orWhere('branch', 'LIKE', "%{$search}%")
                                ->orWhere('acronym', 'LIKE', "%{$search}%")
                        )
                        ->orWhereHas(
                            'user',
                            fn($user)
                            =>
                            $user->where('firstName', 'LIKE', "%{$search}%")
                                ->orWhere('lastName', 'LIKE', "%{$search}%")
                        )
                )
            )
            ->when(
                $status,
                fn($query)
                =>
                $query->where('status', $status)
            )
            ->when(
                $date_to && $date_from,
                fn($query)
                =>
                $query->whereBetween('created_at', [$date_from, $date_to])
                    ->orWhereBetween('created_at', [$date_to, $date_from])
            )
            ->whereRelation('sharedUsers', 'user_id', Auth::id())
            ->paginate($per_page);

        return $requests->through(fn($requestReport) => [
            'id'                          => $requestReport->id,
            'user_id'                     => $requestReport->user_id,
            'form_type'                   => $requestReport->form_type,
            'form_data'                   => $requestReport->form_data,
            'created_at'                  => $requestReport->created_at,
            'updated_at'                  => $requestReport->updated_at,
            'user'                        => $requestReport->user,
            'currency'                    => $requestReport->currency,
            'branch'                      => [
                'name'                    => (($requestReport->branchCode->acronym === "HO" ? 'ㅤ' : 'ㅤ' . $requestReport->branchCode->acronym . " - ") . $requestReport->branchCode?->branch_name . 'ㅤ'),
                'branch'                  => $requestReport->branchCode?->branch
            ],
            'status'                      => $requestReport->status,
            'attachment'                  => $requestReport->attachment,
            'branch_code'                 => $requestReport->branchCode,
            'request_code'                => $requestReport->request_code,
            'completed_code'              => $requestReport->completed_code,
            'requested_by'                => ($requestReport->user ? "{$requestReport->user->firstName} {$requestReport->user->lastName}" : "Unknown"),
            'requested_signature'         => ($requestReport->user ? "{$requestReport->user->signature}" : "Unknown"),
            'requested_position'          => ($requestReport->user ? "{$requestReport->user->position}" : "Unknown"),
            'approved_bies'               => $requestReport->approvalProcess
                ->whereIn('user_id', $requestReport->approved_by)
                ->map(fn($process)        => [
                    'comment'             => $process->comment,
                    'firstName'           => $process->user->firstName,
                    'lastName'            => $process->user->lastName,
                    'position'            => $process->user->position,
                    'signature'           => $process->user->signature,
                    'status'              => $process->status

                ])
                ->values(),
            'noted_bies'                  => $requestReport->approvalProcess
                ->whereIn('user_id', $requestReport->noted_by)
                ->map(fn($process) => [
                    'comment'             => $process->comment,
                    'firstName'           => $process->user->firstName,
                    'lastName'            => $process->user->lastName,
                    'position'            => $process->user->position,
                    'signature'           => $process->user->signature,
                    'status'              => $process->status

                ])
                ->values(),
            'approval_process'            => $requestReport->approvalProcess->whereNotNull('comment')->values(),
            'approved_attachments'        => $requestReport->approvalProcess->whereNotNull('attachment')->values()
                ->pluck('attachment')
        ]);
    }

    public function storeSharedRequest($request)
    {
        $request_form = RequestForm::findOrFail($request->request_id);

        $request_form->sharedUsers()->syncWithoutDetaching($request->user_ids);

        $sender = Auth::user()->branchCode->branch_code . ' - ' . Auth::user()->full_name;

        Notification::send($request_form->sharedUsers, new SharedRequestNotification("{$sender} has shared a request with a request code of {$request_form->request_code}.", $request->request_id, 'Shared a Request', $request_form->request_code));

        $total_users_shared = count($request->user_ids);

        return [$request_form, $total_users_shared];
    }

    public function delete($request, $user_id)
    {
        return $request->sharedUsers()->detach($user_id);
    }

    public function getAllRequestSharedUsers($request)
    {
        return [
            'request_title' => "Users Who Received the Request by {$request->user->fullName} - {$request->user->branchCode->branch_code} with request code of {$request->request_code}",
            'shared_users' => $request->sharedUsers()
                ->with('branchCode')
                ->get()
        ];
    }

    public function listsOfUsersToShareRequest($request_id)
    {
        return User::query()
            ->with('branchCode:id,branch_name,branch_code')
            ->where(
                fn($query)

                =>
                $query->where('id', '!=', Auth::id())
                    ->where('role', '!=', 'Admin')
                    ->whereDoesntHaveRelation('sharedRequests', 'request_form_id', $request_id)
            )
            ->orderBy('lastName')
            ->get(['id', 'firstName', 'lastName', 'position', 'branch_code']);
    }
}