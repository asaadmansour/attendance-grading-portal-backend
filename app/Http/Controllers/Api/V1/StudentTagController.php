<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StudentTag;
use App\Http\Requests\StoreStudentTagRequest;

class StudentTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $student)
    {
        $studentUser = User::findOrFail($student);
        $studentTags = StudentTag::with(['student','tag','course','assignedBy'])->where('student_id',$studentUser->id)->get();
        return response()->json([
            'data'=>$studentTags
        ],200);      
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentTagRequest $request, string $student)
    {
        $studentUser = User::findOrFail($student);
        $studentTag = new StudentTag($request->validated());
        $studentTag->student_id  = $studentUser->id;
        $studentTag->assigned_by = auth()->id();
        $studentTag->save();

        return response()->json(['data' => $studentTag], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $studentTag = StudentTag::findOrFail($id);
        $studentTag->delete();
        return response()->json(['data' => 'Deleted successfully'], 200);
    }
}
