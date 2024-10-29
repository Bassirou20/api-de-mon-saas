<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProduitRequest;
use App\Http\Resources\ProduitResource;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $produits = Produit::with(['fournisseur', 'categorie'])->get();
    
            return response()->json(ProduitResource::collection($produits), 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
    
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération des produits.',
                'error' => $e->getMessage(),
            ], 500);
        }
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProduitRequest $request)
    {
        $validated = $request->validated();
    
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $validated['image'] = 'images/' . $imageName;
        }
    
        $produit = Produit::create($validated);
    
        $newProduit = new ProduitResource($produit);
    
        return response()->json($newProduit, 201);
        drakify('success');
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return  response()->json(Produit::find($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $produit = Produit::findOrFail($id);
            $produit->delete();

            return response()->json([
                'message' => 'Produit supprimé avec succès.',
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'message' => 'Une erreur est survenue lors de la suppression du produit.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
