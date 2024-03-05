<?php

namespace App\Policies;

use App\Models\TeslaAccount;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeslaAccountPolicy
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
    public function view(User $user, TeslaAccount $teslaAccount): bool
    {
        return $user->currentTeam->userHasPermission($user, 'read') && $teslaAccount->team->is($user->currentTeam);
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
    public function update(User $user, TeslaAccount $teslaAccount): bool
    {
        return $user->currentTeam->userHasPermission($user, 'update') && $teslaAccount->team->is($user->currentTeam);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TeslaAccount $teslaAccount): bool
    {
        return $user->currentTeam->userHasPermission($user, 'delete') && $teslaAccount->team->is($user->currentTeam);
    }
}
