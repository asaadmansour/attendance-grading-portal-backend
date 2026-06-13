<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
     * Update a user the caller manages (BM → track admins, TA → instructors/students).
     * Role is intentionally immutable here to keep the created-by hierarchy intact.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'expires_at' => 'sometimes|nullable|date|after:now',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        // a blank password field means "leave it unchanged"
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return $this->ok([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], 'User updated');
    }

    /**
     * Remove a user the caller manages.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return $this->ok(null, 'User deleted');
    }
}
