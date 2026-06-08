<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// Gère l'inscription, la connexion et la déconnexion via l'API Sanctum
class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 'confirmed' exige la présence d'un champ 'password_confirmation' identique au mot de passe
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // Hash::make() crypte le mot de passe — il n'est jamais stocké en clair
            'password' => Hash::make($request->password),
        ]);

        // Crée un token Sanctum lié à l'utilisateur ; 'auth_token' est juste un label lisible
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Hash::check() compare le mot de passe en clair avec le hash stocké
        // On regroupe les deux conditions pour ne pas révéler si l'e-mail existe
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants et/ou le mot de passe sont incorrects.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // Supprime uniquement le token utilisé pour cette requête, pas tous les tokens de l'utilisateur
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}
