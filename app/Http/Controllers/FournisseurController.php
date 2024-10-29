<?php

namespace App\Http\Controllers;

use App\Http\Resources\FournisseurResource;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $fournisseur = Fournisseur::all();
    
            return response()->json(FournisseurResource::collection($fournisseur), 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
    
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des fournisseurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fournisseur=Fournisseur::create([
            'nom'=>$request->nom,
            'adresse'=>$request->adresse,
            'contact'=>$request->contact,
            'description'=>$request->description
        ]);

        $boutique= new FournisseurResource($fournisseur);

        return response()->json($boutique);
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
}
