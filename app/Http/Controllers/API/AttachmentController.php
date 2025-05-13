<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'attachment' => 'required|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            $attachmentFile = $request->file('attachment');
            $path = $attachmentFile->store('request_form_attachments', 'd_drive');
            $filename = $attachmentFile->getClientOriginalName();

            $createdAttachment = Attachment::create([
                'file_name' => $filename,  // Include file_name here
                'file_path' => $path,
            ]);

            return response()->json([
                'message' => 'Attachment uploaded successfully',
                'attachment_id' => $createdAttachment->id,
            ], 201);
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'message' => 'Failed to upload attachment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getFile($filePath)
    {
        // Assuming $filePath is something like 'rattachment/l1oNfvqICCiwpxao9a8YbfaJxPlgMjpC0EPdWHJZ.png'
        $fullPath = Storage::disk('d_drive')->path($filePath);

        if (!File::exists($fullPath)) {
            abort(404, 'File not found');
        }

        // Get the file content and mime type
        $file = File::get($fullPath);
        $type = File::mimeType($fullPath);
        $filename = basename($fullPath);

        return response($file, 200)
            ->header("Content-Type", $type)
            ->header("Content-Disposition", "attachment; filename=\"$filename\"");
    }
}
