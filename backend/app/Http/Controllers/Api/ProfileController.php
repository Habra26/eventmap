<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// Gère la lecture et la modification du profil de l'utilisateur connecté
class ProfileController extends Controller
{
    // Retourne les données de l'utilisateur authentifié (les champs cachés comme 'password' sont exclus)
    public function show(Request $request)
    {
        return $request->user();
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // unique:users sauf pour l'utilisateur lui-même, sinon il ne pourrait pas conserver son e-mail
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour',
            'user' => $user,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Vérifie que l'utilisateur connaît son mot de passe actuel avant d'autoriser le changement
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Mot de passe mis à jour']);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        // On supprime d'abord tous les tokens Sanctum pour invalider les sessions actives
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Compte supprimé']);
    }
}
