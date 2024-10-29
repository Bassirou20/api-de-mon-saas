<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserRessource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function username()
    {
        return 'telephone';
    }

    public function login(Request $request)
    {
        $request->validate([
            'telephone' => 'required|numeric',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['telephone' => $request->telephone, 'password' => $request->password])) {
            $user = Auth::user();

            if (!in_array($user->role->libelle, ['Super-Admin', 'Employe'])) {
                return response()->json([
                    'message' => 'Accès refusé. Seuls les super-admins et employés peuvent se connecter.',
                ], 403);
            }

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'Authentification réussie',
                'user' => new UserRessource($user),
                'token' => $token,
            ]);
        }

        return response()->json([
            'message' => 'Numéro de téléphone ou mot de passe incorrect',
        ], 401);
    }
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
