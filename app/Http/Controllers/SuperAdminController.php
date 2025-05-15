<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    /**
     * Show the reward redemptions management page.
     *
     * @return \Illuminate\View\View
     */
    public function rewardIndex()
    {
        return view('super-admin.show-reward-redemptions');
    }

    /**
     * Show the user list management page.
     *
     * @return \Illuminate\View\View
     */
    public function userIndex()
    {
        return view('super-admin.show-user-list');
    }

    /**
     * Show detailed user profile page.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function userProfile(User $user)
    {
        // Use route model binding to get the user
        // Include with trashed users so we can view archived profiles
        if ($user->trashed()) {
            $user = User::withTrashed()->findOrFail($user->id);
        }
        
        return view('super-admin.user-profile', compact('user'));
    }
    
    /**
     * Toggle user active status
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own account status.');
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User has been {$status} successfully.");
    }
    
    /**
     * Archive a user (soft delete)
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function archiveUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot archive your own account.');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User has been archived successfully.');
    }
    
    /**
     * Restore an archived user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreUser($userId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();
        
        return back()->with('success', 'User has been restored successfully.');
    }
}
