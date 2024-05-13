<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    private $providers = ['github', 'google', 'facebook', 'discord'];

    public function redirectToProvider(Request $request, $provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Provedor não suportado');
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        if (!in_array($provider, $this->providers)) {
            return redirect('/login')->with('error', 'Provedor não suportado');
        }

        $user = Socialite::driver($provider)->user();
        $this->handleUser($user);

        return redirect('/dashboard');
    }

    private function handleUser($user)
    {
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            $this->updateUser($existingUser, $user);
        } else {
            $this->createUser($user);
        }
    }

    private function updateUser($existingUser, $user)
    {
        $existingUser->name = $user->getName();
        $existingUser->email = $user->getEmail();
        $existingUser->save();
        auth()->login($existingUser, true);
    }

    private function createUser($user)
    {
        $newUser = User::create([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => Hash::make(Str::random(16)), // Senha aleatória
        ]);
        auth()->login($newUser, true);

        $this->createTeam($newUser);
    }

    private function createTeam($newUser)
    {
        $newUser->ownedTeams()->save(Team::forceCreate([
            'user_id' => $newUser->id,
            'name' => explode(' ', $newUser->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
