<?php

namespace App\Http\Controllers;

use App\Http\Requests\FournisseurRequest;
use App\Http\Resources\FournisseurResource;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    public function store(FournisseurRequest $request)
    {
       
        $validated = $request->validated();
    
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $validated['image'] = 'images/' . $imageName;
        }
    
        $produit = Fournisseur::create($validated);
    
        $newProduit = new FournisseurResource($produit);
    
        return response()->json($newProduit, 201);
        drakify('success');
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
        return DB::transaction(function () use ($request, $id) {
            $fournisseur = Fournisseur::findOrFail($id);
    
            $validatedData = $request->validate([
                'nom' => 'string|max:255',
                'adresse' => 'string|max:255',
                'contact' => 'string|max:255',
                'description' => 'nullable',
                'image' => 'nullable'
            ]);

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('images'), $imageName);
                $validated['image'] = 'images/' . $imageName;
            }

            
    
            $fournisseur->update($validatedData);
    
            return new FournisseurResource($fournisseur);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $produit = Fournisseur::findOrFail($id);
            $produit->delete();

            return response()->json([
                'message' => 'Fournisseur  supprimée avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression du fournisseur.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
