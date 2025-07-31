<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class UserController extends Controller
{
    use ApiResponse;

    // Get all active users
    public function index()
    {
        $users = User::whereNull('deleted_at')->simplePaginate(10);
        return $this->success($users, 'Users retrieved successfully');
    }

    // Get single user by ID
    public function show($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success($user, 'User retrieved successfully');
    }

    // Suspend a user
    public function suspend($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $user->is_suspended = true;
        $user->save();

        return $this->success($user, 'User suspended successfully');
    }

    // Unsuspend a user
    public function unsuspend($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $user->is_suspended = false;
        $user->save();

        return $this->success($user, 'User unsuspended successfully');
    }

    // Soft delete user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }

    // Restore (undelete) user
    public function restore($id)
    {
        $user = User::onlyTrashed()->find($id);

        if (!$user) {
            return $this->error('Deleted user not found', 404);
        }

        $user->restore();

        return $this->success($user, 'User restored successfully');
    }

    // Get all suspended users
    public function suspendedUsers()
    {
        $users = User::where('is_suspended', true)
            ->whereNull('deleted_at')
            ->simplePaginate(10);

        return $this->success($users, 'Suspended users retrieved successfully');
    }

    // Get all soft-deleted users
    public function deletedUsers()
    {
        $users = User::onlyTrashed()->simplePaginate(10);
        return $this->success($users, 'Deleted users retrieved successfully');
    }
}
