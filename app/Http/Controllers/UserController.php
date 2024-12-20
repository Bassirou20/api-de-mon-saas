<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserRessource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $UserRessource=User::with('role')->get();
        return UserRessource::collection($UserRessource);
    }

    public function getClients()
    {
        $clients = User::withCount(['commandes'])
        ->whereHas('role', function($query) {
            $query->where('libelle', 'Client');
        })->get();

        return response()->json($clients);
    }

    

    public function getLivreurs()
    {
        // Filtrer par le libellé 'livreur' dans la relation role
        $livreurs = User::whereHas('role', function($query) {
            $query->where('libelle', 'livreur');
        })->get();

        return response()->json($livreurs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $user=User::create([
            'nom'=>$request->nom,
            'prenom'=>$request->prenom,
            'email'=>$request->email,
            'password'=>$request->password,
            'telephone'=>$request->telephone,
            'adresse'=>$request->adresse,
            'role_id'=>$request->role_id,
            // 'isActive'=>$request->isActive
        ]);

         $UserRessource = new UserRessource($user) ;
        return response()->json(['message'=>'User created successfully','user'=>$UserRessource]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function toggleActiveStatus($id)
{
    $user = User::findOrFail($id);
    $user->isActive = !$user->isActive;  
    $user->save();

    return response()->json(['message' => 'User status updated successfully', 'statut' => $user->statut]);
}

}
