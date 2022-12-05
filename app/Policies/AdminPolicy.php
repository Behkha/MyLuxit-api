<?php

namespace App\Policies;

use App\Models\Admin;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function create(Admin $admin)
    {
        return in_array(Admin::PRIVILEGES['master'], $admin->privileges);
    }

    public function update(Admin $admin)
    {
        return in_array(Admin::PRIVILEGES['master'], $admin->privileges);
    }

    public function delete(Admin $admin)
    {
        return in_array(Admin::PRIVILEGES['master'], $admin->privileges);
    }
}
