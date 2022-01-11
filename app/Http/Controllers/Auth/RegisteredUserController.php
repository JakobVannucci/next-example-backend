<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AdminLevel;
use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user.name' => 'required|string|max:300',
            'user.email' => 'required|string|email|max:300|unique:users,email',
            'user.password' => 'required|string|confirmed|min:8',
            'organisation.name' => 'required|string|max:300'
        ]);

        $user = DB::transaction(function () use ($validated) {
            $organisationData = Arr::pull($validated, 'organisation');

            $userData = Arr::pull($validated, 'user');

            $organisation = Organisation::create($organisationData);

            $user = User::create(array_merge($userData, ['active_organisation_id' => $organisation->id, 'password' => Hash::make($userData['password'])]));

            OrganisationUser::create([
                'organisation_id' => $organisation->id,
                'user_id' => $user->id,
                'admin_level' => AdminLevel::Admin
            ]);

            return $user;
        });

        Auth::login($user);

        event(new Registered($user));


        return response()->noContent();
    }
}
