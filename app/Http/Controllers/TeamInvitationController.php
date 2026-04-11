<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamInvitationController extends Controller
{
    /**
     * Show the accept-invitation page.
     */
    public function show(string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired()) {
            abort(410, 'This invitation has expired.');
        }

        if ($invitation->accepted_at) {
            return redirect('/admin')->with('info', 'This invitation has already been accepted.');
        }

        return view('auth.accept-invitation', compact('invitation'));
    }

    /**
     * Process acceptance: create user account and attach to team.
     */
    public function accept(Request $request, string $token)
    {
        $invitation = TeamInvitation::where('token', $token)->firstOrFail();

        if ($invitation->isExpired() || $invitation->accepted_at) {
            abort(410, 'This invitation is no longer valid.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create or update user
        $user = User::firstOrCreate(
            ['email' => $invitation->email],
            [
                'name'     => $request->name,
                'password' => Hash::make($request->password),
                'role'     => $invitation->role,
                'team_id'  => $invitation->team_id,
                'status'   => 'active',
            ]
        );

        // Ensure user is in this team
        $invitation->team->users()->syncWithoutDetaching([
            $user->id => ['role_override' => $invitation->role],
        ]);

        if ($user->team_id === null) {
            $user->update(['team_id' => $invitation->team_id]);
        }

        $invitation->update(['accepted_at' => now()]);

        auth()->login($user);

        return redirect('/admin')->with('success', 'Welcome to ' . $invitation->team->name . '!');
    }
}
