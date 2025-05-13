<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Approver;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Log the ID of the user being updated
        /* Log::info('User updated: ' . $user->id);

        if ($user->isDirty('role')) {
            if ($user->role === 'approver') {
                $approver = Approver::firstOrNew(['user_id' => $user->id]);
                $this->updateApproverFromUser($approver, $user); // Update or create Approver
                $approver->save();
            } elseif ($user->role === 'User') {
                // Delete the Approver record if the role changes to User
                Approver::where('user_id', $user->id)->delete();
            }
        } */
    }

    /**
     * Update the Approver model from the User attributes.
     *
     * @param \App\Models\Approver $approver
     * @param \App\Models\User $user
     * @return void
     */
    private function updateApproverFromUser(Approver $approver, User $user): void
    {
        $approver->firstname = $user->firstName;
        $approver->lastname = $user->lastName;
        $approver->email = $user->email;
        $approver->position = $user->position;
        $approver->role = $user->role;
        $approver->branch_code = $user->branch_code;
        $approver->signature = $user->signature;
        $approver->employee_id = $user->employee_id;
        $approver->branch = $user->branch;
        
        // Add more attributes as necessary
    }
}
