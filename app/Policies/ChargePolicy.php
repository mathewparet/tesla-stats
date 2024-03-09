<?php

namespace App\Policies;

use App\Models\Charge;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChargePolicy
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
    public function view(User $user, Charge $charge): bool
    {
        return $user->currentTeam->userHasPermission($user, 'read') && $charge->vehicle->team->is($user->currentTeam);
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
    public function update(User $user, Charge $charge): bool
    {
        return $user->currentTeam->userHasPermission($user, 'update') && $charge->vehicle->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Charge $charge): bool
    {
        return $user->currentTeam->userHasPermission($user, 'delete') && $charge->vehicle->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Charge $charge): bool
    {
        return $user->currentTeam->userHasPermission($user, 'delete') && $charge->vehicle->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Charge $charge): bool
    {
        return $user->currentTeam->userHasPermission($user, 'delete') && $charge->vehicle->team->is($user->currentTeam);
    }
}
