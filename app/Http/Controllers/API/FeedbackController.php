<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use App\Notifications\NotifyAllUsersNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page ?: 10;
        $feedbacks = Feedback::orderBy("created_at", "desc")
            ->paginate($per_page);

        return response()->json($feedbacks, 200);
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
            "name"              => ["required", "string", "max:255", "min:5"],
            "email"             => ["required", "email", "max:255", "min:5"],
            "phone"             => ["nullable", "digits:11"],
            "department"        => ["required", "max:255", "min:5"],
            "opinion"           => ["required", "max:255", "min:5"],
            "other_opinion"     => ["nullable", "required_if:opinion,other", "max:255", "min:5"],
            "message"           => ["required", "max:255", "min:2"],
        ]);

        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }

        do {
            $feedback_code = "SMCT-" . Str::random(50);
        } while (Feedback::where("feedback_code", $feedback_code)->first());

        $feedback = Feedback::create([
            "name"              => $request->name,
            "email"             => Str::lower($request->email),
            "phone"             => $request->phone,
            "department"        => $request->department,
            "opinion"           => $request->opinion,
            "other_opinion"     => $request->other_opinion,
            "message"           => $request->message,
            "feedback_code"     => Str::upper($feedback_code)
        ]);

        return response()->json([
            "message"           => "Your feedback has been submitted successfully. We will contact you soon.",
            "feedback_code"     => $feedback->feedback_code
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function notifyAllUserForFeedback(Request $request)
    {
        if (config('app.env') === 'local') {
            ini_set('max_execution_time', 300);
        }

        $request->validate([
            'title'     => ['required', 'min:2', 'max:50'],
            'message'   => ['required', 'min:2', 'max:500']
        ]);

        $usersCount = User::query()
            ->whereNot('id', Auth::id())
            ->count();

        User::whereNot('id', Auth::id())->chunk(100, function ($users) use ($request) {
            Notification::send($users, new NotifyAllUsersNotification($request->message, Auth::user()->full_name, $request->title, 'feedback'));
        });

        return response()->json([
            'message'       => "Notification sent {$usersCount} successfully",
        ], 201);
    }
}
