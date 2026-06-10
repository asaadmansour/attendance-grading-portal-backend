<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ComponentGrade;
use App\Models\CourseComponent;
use App\Models\GradeOverride;
use App\Http\Requests\StoreGradeOverrideRequest;


class GradeOverrideController extends Controller
{

    public function override(StoreGradeOverrideRequest $request, string $componentGrade)
    {
        $grade = ComponentGrade::findOrFail($componentGrade);
        $data  = $request->validated();

        $component = CourseComponent::findOrFail($grade->course_component_id);
        abort_if($data['new_value'] > $component->weight, 422, 'new_value exceeds the component weight (maximum normalized score).');

        $override = DB::transaction(function () use ($grade, $data) {

            $newOverride = new GradeOverride($data);
            $newOverride->component_grade_id = $grade->id;
            $newOverride->original_value     = $grade->normalized_score;
            $newOverride->overridden_by      = auth()->id();
            $newOverride->save();

            $grade->normalized_score = $data['new_value'];
            $grade->save();

            return $newOverride;
        });

        return response()->json(['data' => $override], 201);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(string $grade)
    {
        $componentGrade = ComponentGrade::findOrFail($grade);
        $audit = GradeOverride::with(['componentGrade','overriddenBy'])->where('component_grade_id' , $componentGrade->id)->get();
        return response()->json([
            'data'=>$audit
        ],200); 
    }

}
