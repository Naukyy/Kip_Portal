<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    /**
     * Display list of users with search functionality
     * GET /admin/users
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $role = $request->get('role', '');
        
        $users = User::when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%")
                      ->orWhere('nickname', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderBy('employee_code')
            ->paginate(20);
            
        return view('admin.users.index', compact('users', 'search', 'role'));
    }

/**
     * AJAX: Search users for autocomplete or quick search
     * GET /admin/users/search
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');
        
$users = User::where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%")
                      ->orWhere('nickname', 'like', "%{$search}%");
            })
            ->select('id', 'employee_code', 'name', 'nickname', 'whatsapp', 'email', 'role', 'is_active')
            ->limit(10)
            ->get();
            
        return response()->json(['users' => $users]);
    }

    /**
     * AJAX: Get single user data for edit modal
     * GET /admin/users/{user}
     */
    public function show(Request $request, User $user): JsonResponse
    {
        return response()->json([
            'id' => $user->id,
            'employee_code' => $user->employee_code,
            'name' => $user->name,
            'nickname' => $user->nickname,
            'whatsapp' => $user->whatsapp,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * AJAX: Store new user
     * POST /admin/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                $data['password'] = Hash::make('password123'); // Default password
            }
            
            $user = User::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan!',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan user: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * AJAX: Update existing user
     * PUT /admin/users/{user}
     */
    public function update(StoreUserRequest $request, User $user): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Password logic: empty = keep old, filled = update with new hash
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
            
            // Remove password_confirmation from data
            unset($data['password_confirmation']);
            
            $user->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui!',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * AJAX: Delete user
     * DELETE /admin/users/{user}
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun sendiri!'
            ], 422);
        }
        
        try {
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * AJAX: Reset user password
     * POST /admin/users/{user}/reset-password
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        
        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset password: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * AJAX: Toggle user active status
     * POST /admin/users/{user}/toggle-status
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menonaktifkan akun sendiri!'
            ], 422);
        }
        
        try {
            $user->update([
                'is_active' => !$user->is_active,
            ]);
            
            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            return response()->json([
                'success' => true,
                'message' => "User berhasil {$status}!",
                'is_active' => $user->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 422);
        }
    }
}
