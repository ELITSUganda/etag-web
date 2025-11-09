<?php

namespace App\Http\Controllers;

use App\Models\AdminRoleUser;
use App\Models\Location;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ApiUserManagementController extends Controller
{
    use ApiResponser;

    /**
     * Get authenticated user's full profile with all relationships
     * GET /api/user/profile
     */
    public function getProfile(Request $request)
    {
        try {
            $user_id = ((int)(Utils::get_user_id($request)));
            $user = Administrator::with(['roles', 'permissions'])->find($user_id);

            if (!$user) {
                return $this->error('User not found', 404);
            }

            // Add additional computed fields
            $user->role_names = $user->roles->pluck('name')->toArray();
            $user->role_slugs = $user->roles->pluck('slug')->toArray();
            $user->permission_names = $user->permissions->pluck('name')->toArray();
            
            // Get sub-county and district info
            if ($user->sub_county_id) {
                $subCounty = Location::find($user->sub_county_id);
                if ($subCounty) {
                    $user->sub_county_name = $subCounty->name_text;
                    if ($subCounty->parent) {
                        $district = Location::find($subCounty->parent);
                        if ($district) {
                            $user->district_name = $district->name_text;
                        }
                    }
                }
            }

            // Get district info if district_id is set
            if ($user->district_id) {
                $district = Location::find($user->district_id);
                if ($district) {
                    $user->district_name = $district->name_text;
                }
            }

            return $this->success($user, 'Profile retrieved successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to retrieve profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user profile
     * POST /api/user/update-profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user_id = ((int)(Utils::get_user_id($request)));
            $user = Administrator::find($user_id);

            if (!$user) {
                return $this->error('User not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:admin_users,email,' . $user->id,
                'phone_number' => 'sometimes|string',
                'phone_number_2' => 'sometimes|string|nullable',
                'address' => 'sometimes|string|nullable',
                'nin' => 'sometimes|string|nullable',
                'gender' => 'sometimes|in:Male,Female',
                'sub_county_id' => 'sometimes|integer',
                'district_id' => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
            }

            // Update basic info
            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
            }
            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
            }
            if ($request->has('name')) {
                $user->name = $request->name;
            } elseif ($request->has('first_name') || $request->has('last_name')) {
                // Auto-generate name from first and last name
                $user->name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            }

            if ($request->has('email')) {
                $user->email = $request->email;
            }

            if ($request->has('phone_number')) {
                $phone = Utils::prepare_phone_number($request->phone_number);
                if (Utils::phone_number_is_valid($phone)) {
                    // Check if phone number is already used by another user
                    $existing = Administrator::where('phone_number', $phone)
                        ->where('id', '!=', $user->id)
                        ->first();
                    if ($existing) {
                        return $this->error('Phone number already in use by another account', 422);
                    }
                    $user->phone_number = $phone;
                } else {
                    return $this->error('Invalid phone number format', 422);
                }
            }

            if ($request->has('phone_number_2')) {
                if (!empty($request->phone_number_2)) {
                    $phone2 = Utils::prepare_phone_number($request->phone_number_2);
                    if (Utils::phone_number_is_valid($phone2)) {
                        $user->phone_number_2 = $phone2;
                    }
                } else {
                    $user->phone_number_2 = null;
                }
            }

            if ($request->has('address')) {
                $user->address = $request->address;
            }
            if ($request->has('nin')) {
                $user->nin = $request->nin;
            }
            if ($request->has('gender')) {
                $user->gender = $request->gender;
            }
            if ($request->has('sub_county_id')) {
                $user->sub_county_id = $request->sub_county_id;
                // Auto-set district from sub-county
                $subCounty = Location::find($request->sub_county_id);
                if ($subCounty && $subCounty->parent) {
                    $user->district_id = $subCounty->parent;
                }
            }
            if ($request->has('district_id')) {
                $user->district_id = $request->district_id;
            }

            // Handle avatar upload
            if (!empty($_FILES)) {
                try {
                    $image = Utils::upload_images_2($_FILES, true);
                    if ($image && strlen($image) > 4) {
                        $user->avatar = 'public/storage/images/' . $image;
                    }
                } catch (Throwable $t) {
                    // Continue without avatar update if upload fails
                }
            }

            $user->save();

            return $this->success($user, 'Profile updated successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Change password
     * POST /api/user/change-password
     */
    public function changePassword(Request $request)
    {
        try {
            $user_id = ((int)(Utils::get_user_id($request)));
            $user = Administrator::find($user_id);

            if (!$user) {
                return $this->error('User not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:4|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
            }

            // Verify current password
            if (!password_verify($request->current_password, $user->password)) {
                return $this->error('Current password is incorrect', 422);
            }

            // Update password
            $user->password = password_hash($request->new_password, PASSWORD_DEFAULT);
            $user->save();

            return $this->success(null, 'Password changed successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to change password: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reset password (admin function)
     * POST /api/user/reset-password
     */
    public function resetPassword(Request $request)
    {
        try {
            $admin_id = ((int)(Utils::get_user_id($request)));
            $admin = Administrator::find($admin_id);

            if (!$admin || !$admin->isRole('administrator')) {
                return $this->error('Unauthorized', 403);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:admin_users,id',
                'new_password' => 'required|string|min:4',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
            }

            $user = Administrator::find($request->user_id);
            $user->password = password_hash($request->new_password, PASSWORD_DEFAULT);
            $user->save();

            return $this->success(null, 'Password reset successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to reset password: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all users with filters and search
     * GET /api/users/list
     */
    public function listUsers(Request $request)
    {
        try {
            $query = Administrator::query();

            // Search by name, phone, email, username
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('phone_number', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('username', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by user type
            if ($request->has('user_type')) {
                $query->where('user_type', $request->user_type);
            }

            // Filter by gender
            if ($request->has('gender')) {
                $query->where('gender', $request->gender);
            }

            // Filter by district
            if ($request->has('district_id')) {
                $query->where('district_id', $request->district_id);
            }

            // Filter by sub-county
            if ($request->has('sub_county_id')) {
                $query->where('sub_county_id', $request->sub_county_id);
            }

            // Filter by role
            if ($request->has('role_id')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('role_id', $request->role_id);
                });
            }

            // Filter by role slug
            if ($request->has('role_slug')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('slug', $request->role_slug);
                });
            }

            // Date filters
            if ($request->has('created_from')) {
                $query->where('created_at', '>=', $request->created_from);
            }
            if ($request->has('created_to')) {
                $query->where('created_at', '<=', $request->created_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);

            // Pagination
            $perPage = $request->get('per_page', 20);
            $users = $query->paginate($perPage);

            // Add roles to each user
            $users->getCollection()->transform(function ($user) {
                $user->roles_list = $user->roles->pluck('name')->toArray();
                return $user;
            });

            return $this->success([
                'users' => $users->items(),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ], 'Users retrieved successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single user details
     * GET /api/users/{id}
     */
    public function getUserDetails(Request $request, $id)
    {
        try {
            $user = Administrator::with(['roles', 'permissions'])->find($id);

            if (!$user) {
                return $this->error('User not found', 404);
            }

            // Add computed fields
            $user->role_names = $user->roles->pluck('name')->toArray();
            $user->role_slugs = $user->roles->pluck('slug')->toArray();

            // Get location names
            if ($user->sub_county_id) {
                $subCounty = Location::find($user->sub_county_id);
                if ($subCounty) {
                    $user->sub_county_name = $subCounty->name_text;
                }
            }
            if ($user->district_id) {
                $district = Location::find($user->district_id);
                if ($district) {
                    $user->district_name = $district->name_text;
                }
            }

            return $this->success($user, 'User details retrieved successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user roles
     * POST /api/users/{id}/roles
     */
    public function updateUserRoles(Request $request, $id)
    {
        try {
            $admin_id = ((int)(Utils::get_user_id($request)));
            $admin = Administrator::find($admin_id);

            if (!$admin || !$admin->isRole('administrator')) {
                return $this->error('Unauthorized', 403);
            }

            $user = Administrator::find($id);
            if (!$user) {
                return $this->error('User not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'role_ids' => 'required|array',
                'role_ids.*' => 'integer|exists:admin_roles,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
            }

            // Clear existing roles
            AdminRoleUser::where('user_id', $user->id)->delete();

            // Assign new roles
            foreach ($request->role_ids as $roleId) {
                AdminRoleUser::create([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Reload user with roles
            $user = Administrator::with('roles')->find($id);
            $user->role_names = $user->roles->pluck('name')->toArray();

            return $this->success($user, 'User roles updated successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to update roles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add role to user
     * POST /api/users/{id}/roles/add
     */
    public function addRoleToUser(Request $request, $id)
    {
        try {
            $admin_id = ((int)(Utils::get_user_id($request)));
            $admin = Administrator::find($admin_id);

            if (!$admin || !$admin->isRole('administrator')) {
                return $this->error('Unauthorized', 403);
            }

            $user = Administrator::find($id);
            if (!$user) {
                return $this->error('User not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'role_id' => 'required|integer|exists:admin_roles,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
            }

            // Check if role already assigned
            $existing = AdminRoleUser::where('user_id', $user->id)
                ->where('role_id', $request->role_id)
                ->first();

            if ($existing) {
                return $this->error('User already has this role', 422);
            }

            // Assign role
            AdminRoleUser::create([
                'user_id' => $user->id,
                'role_id' => $request->role_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user = Administrator::with('roles')->find($id);
            $user->role_names = $user->roles->pluck('name')->toArray();

            return $this->success($user, 'Role added successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to add role: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove role from user
     * POST /api/users/{id}/roles/remove
     */
    public function removeRoleFromUser(Request $request, $id)
    {
        try {
            $admin_id = ((int)(Utils::get_user_id($request)));
            $admin = Administrator::find($admin_id);

            if (!$admin || !$admin->isRole('administrator')) {
                return $this->error('Unauthorized', 403);
            }

            $user = Administrator::find($id);
            if (!$user) {
                return $this->error('User not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'role_id' => 'required|integer|exists:admin_roles,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
            }

            // Remove role
            AdminRoleUser::where('user_id', $user->id)
                ->where('role_id', $request->role_id)
                ->delete();

            $user = Administrator::with('roles')->find($id);
            $user->role_names = $user->roles->pluck('name')->toArray();

            return $this->success($user, 'Role removed successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to remove role: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update user status (Activate/Deactivate)
     * POST /api/users/{id}/status
     */
    public function updateUserStatus(Request $request, $id)
    {
        try {
            $admin_id = ((int)(Utils::get_user_id($request)));
            $admin = Administrator::find($admin_id);

            if (!$admin || !$admin->isRole('administrator')) {
                return $this->error('Unauthorized', 403);
            }

            $user = Administrator::find($id);
            if (!$user) {
                return $this->error('User not found', 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', 422, ['errors' => $validator->errors()]);
            }

            $user->status = $request->status;
            $user->save();

            return $this->success($user, 'User status updated successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to update status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all available roles
     * GET /api/roles/list
     */
    public function listRoles(Request $request)
    {
        try {
            $roles = Role::orderBy('name', 'asc')->get();
            return $this->success($roles, 'Roles retrieved successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to retrieve roles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get users by specific role
     * GET /api/users/by-role/{roleSlug}
     */
    public function getUsersByRole(Request $request, $roleSlug)
    {
        try {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) {
                return $this->error('Role not found', 404);
            }

            $query = Administrator::whereHas('roles', function ($q) use ($role) {
                $q->where('role_id', $role->id);
            });

            // Apply search if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('phone_number', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 50);
            $users = $query->paginate($perPage);

            return $this->success([
                'role' => $role,
                'users' => $users->items(),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                ]
            ], 'Users retrieved successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user statistics
     * GET /api/users/statistics
     */
    public function getUserStatistics(Request $request)
    {
        try {
            $stats = [
                'total_users' => Administrator::count(),
                'active_users' => Administrator::where('status', 'Active')->count(),
                'inactive_users' => Administrator::where('status', 'Inactive')->count(),
                'users_by_gender' => [
                    'male' => Administrator::where('gender', 'Male')->count(),
                    'female' => Administrator::where('gender', 'Female')->count(),
                ],
                'recent_registrations' => [
                    'today' => Administrator::whereDate('created_at', today())->count(),
                    'this_week' => Administrator::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                    'this_month' => Administrator::whereMonth('created_at', now()->month)->count(),
                ],
            ];

            // Users by role
            $roles = Role::withCount('administrators')->get();
            $stats['users_by_role'] = $roles->map(function ($role) {
                return [
                    'role' => $role->name,
                    'slug' => $role->slug,
                    'count' => $role->administrators_count,
                ];
            });

            return $this->success($stats, 'Statistics retrieved successfully');
        } catch (Throwable $e) {
            return $this->error('Failed to retrieve statistics: ' . $e->getMessage(), 500);
        }
    }
}
