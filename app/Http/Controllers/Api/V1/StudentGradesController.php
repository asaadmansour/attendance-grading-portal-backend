<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Services\GradingService;

class StudentGradesController extends Controller
{
   

    /**
     * Display the specified resource.
     */
    public function grandTotal(string $student, GradingService $grading)
    {
        $studentUser = User::findOrFail($student);
        if (auth()->user()->role === 'student' && auth()->id() !== $studentUser->id) {
            return $this->fail('You can only view your own grades', 403);
        }
        $result = $grading->grandTotalFor($studentUser);
        return $this->ok($result);
    }

    
}
