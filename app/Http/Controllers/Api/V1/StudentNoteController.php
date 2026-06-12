<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentNote;
use App\Http\Requests\StoreStudentNoteRequest;
class StudentNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $id)
    {
        $notes = StudentNote::with(['student', 'author'])->where('student_id', $id)->get();
        return $this->ok($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentNoteRequest $request, string $student)
    {
        $student = User::findOrFail($student);
        $note = new StudentNote([
            'student_id' => $student->id,
            'body'       => $request->validated()['body'],
        ]);
        $note->author_id = auth()->id();
        $note->save();

        return $this->ok($note, 'Note created', 201);
    }

    
    public function destroy(string $id)
    {
        $note = StudentNote::findOrFail($id);
        $note->delete();
        return $this->ok(null, 'Note deleted');
    }
}
