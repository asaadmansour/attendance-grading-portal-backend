<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = match ($user->role) {
            'branch_manager' => User::where(fn ($q) => $q
                ->where('created_by', $user->id)
                ->orWhereIn('created_by', $user->createdUsers()->pluck('id'))),
            'track_admin' => User::where('created_by', $user->id),
            default => User::whereRaw('1 = 0'),
        };

        // optional ?role= filter for the cohort-setup pickers
        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        return response()->json($query->paginate($request->input('per_page', 15)));
    }
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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
}
