<?php

namespace App\Policies;

use App\Models\BillingProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BillingProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BillingProfile $billingProfile): bool
    {
        return $user->currentTeam->userHasPermission($user, 'read') && $billingProfile->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentTeam->userHasPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BillingProfile $billingProfile): bool
    {
        return $user->currentTeam->userHasPermission($user, 'update') && $billingProfile->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BillingProfile $billingProfile): bool
    {
        return $user->currentTeam->userHasPermission($user, 'delete') && $billingProfile->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BillingProfile $billingProfile): bool
    {
        return $user->currentTeam->userHasPermission($user, 'delete') && $billingProfile->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BillingProfile $billingProfile): bool
    {
        return $user->currentTeam->userHasPermission($user, 'delete') && $billingProfile->team->is($user->currentTeam);
    }
}
