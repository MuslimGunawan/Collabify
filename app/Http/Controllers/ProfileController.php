<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit(): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
        ];

        // Upload custom avatar
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        // Upload custom favicon (only allowed for Main Client)
        $isMainClient = in_array($request->ip(), ['127.0.0.1', '::1'])
            || str_starts_with($request->header('host'), 'localhost')
            || str_starts_with($request->header('host'), '127.0.0.1');

        if ($isMainClient && $request->hasFile('favicon')) {
            Storage::disk('public')->putFileAs('', $request->file('favicon'), 'custom_favicon.png');
        }

        // Update password if filled
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('home')->with('success', 'Profil berhasil diperbarui.');
    }
}
