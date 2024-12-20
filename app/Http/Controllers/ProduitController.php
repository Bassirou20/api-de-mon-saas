<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProduitRequest;
use App\Http\Requests\ReapprovisionnementRequest;
use App\Http\Requests\updateProductRequest;
use App\Http\Resources\ProduitResource;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    // public function update(Request $request, string $id)
    // {
    //     return DB::transaction(function () use ($request, $id) {
    //         $produit = Produit::findOrFail($id);
    
    //         // Validate the request data
    //         $validatedData = $request->validate([
    //             'libelle' => 'string|max:255',
    //             'description' => 'nullable|string',
    //             'quantite' => 'integer',
    //             'fournisseur_id' => 'nullable|exists:fournisseurs,id',
    //             'categorie_id' => 'nullable|exists:categories,id',
    //             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg'
    //         ]);
    
    //         // Handle the image upload
    //         if ($request->hasFile('image')) {
    //             $imageName = time() . '.' . $request->image->extension();
    //             $request->image->move(public_path('images'), $imageName);
    //             $validatedData['image'] = 'images/' . $imageName;
    //         }
            
    //         if (empty($validatedData['categorie_id'])) {
    //             throw new \Exception("categorie_id cannot be null");
    //         }

    //         if (isset($validatedData['image'])) {
    //             $produit->image = $validatedData['image'];
    //         }
            
            
    //         // Update the product data
    //         $produit->update($validatedData);
    
    //         return new ProduitResource($produit);
    //     });
    // }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $produit = Produit::findOrFail($id);
    
            // Log the received data
            Log::info('Received data for update:', $request->all());
    
            // Handle the image upload
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('images'), $imageName);
                $request->merge(['image' => 'images/' . $imageName]);
            }
    
            // Log the data to be updated
            Log::info('Data to be updated:', $request->all());
    
            // Update the product data
            $produit->update($request->all());
    
            // Log the updated product
            Log::info('Updated product:', $produit->toArray());
    
            return new ProduitResource($produit);
        });
    }
    
    
    


    public function reapprovisionner(Request $request)
{
    $validated = $request->validate([
        'produits' => 'required|array',
        'produits.*.id' => 'required|exists:produits,id',
        'produits.*.quantite' => 'required|integer|min:1',
    ]);

    try {
        foreach ($validated['produits'] as $produitData) {
            $produit = Produit::findOrFail($produitData['id']);
            $produit->quantite += $produitData['quantite'];
            $produit->save();
        }

        return response()->json([
            'message' => 'Réapprovisionnement effectué avec succès.',
        ], 200);
    } catch (\Exception $e) {
        Log::error($e->getMessage());

        return response()->json([
            'message' => 'Une erreur est survenue lors du réapprovisionnement.',
            'error' => $e->getMessage(),
        ], 500);
    }
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
