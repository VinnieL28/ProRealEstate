<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LandingController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect('/admin');
        }

        return view('landing.index');
    }

    public function register()
    {
        if (auth()->check()) {
            return redirect('/admin');
        }

        return view('landing.register');
    }

    public function storeRegistration(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        // Create user first (team requires owner_id FK)
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'owner',
            'status'   => 'active',
        ]);

        // Create team with owner — trial_ends_at auto-set in Team::booted()
        $team = Team::create([
            'name'     => $data['company_name'],
            'owner_id' => $user->id,
        ]);

        // Link user to team
        $user->update(['team_id' => $team->id]);
        $team->users()->attach($user->id, ['role_override' => 'owner']);

        // Create default settings for the team
        Setting::create([
            'team_id'      => $team->id,
            'company_name' => $data['company_name'],
        ]);

        auth()->login($user);

        return redirect('/admin/onboarding')->with('success', 'Welcome! Let\'s set up your CRM.');
    }
}
