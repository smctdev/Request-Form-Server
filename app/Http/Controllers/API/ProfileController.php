<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function profile(Request $request)
    {
        $myProfile = $request->user()->load(
            'branch',
            'notedBies.notedBy',
            'approvedBies.approvedBy',
            'requestAccess',
        );

        return response([
            'status'    =>      true,
            'data'      =>      $myProfile,
        ], 200);
    }
}
