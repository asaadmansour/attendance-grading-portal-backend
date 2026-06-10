<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use App\Services\FileStorage;

class SubmissionController extends Controller
{
    public function store(StoreSubmissionRequest $request, Assignment $assignment, FileStorage $storage)
    {
        $filePath = $request->hasFile('file')
            ? $storage->store($request->file('file'), 'submissions')
            : null;

        $submission = Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $request->user()->id],
            [
                'url' => $filePath ? null : $request->input('url'),
                'file_path' => $filePath,
                'submitted_at' => now(),
            ],
        );

        return $this->ok($submission, 'Submission received', 201);
    }
}
