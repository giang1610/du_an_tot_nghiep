<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\Role;

class AssignRoleToUserAfterRegistered
{
    public function handle(Registered $event)
    {
        $user = $event->user;

        // Gán role 'user' (hoặc 'admin' nếu bạn muốn)
        $role = Role::where('name', 'user')->first();

        if ($role) {
            $user->role_id = $role->id;
            $user->save();
        }
    }
}
