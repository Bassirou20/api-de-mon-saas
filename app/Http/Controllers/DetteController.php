<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetteRequest;
use App\Http\Requests\PayerDetteRequest;
use App\Http\Resources\DetteRessource;
use App\Models\Dette;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Loggers\Log as LoggersLog;

class DetteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DetteRequest $request)
    {
        $user = User::find($request->user_id);
    
        if (!$user || !$user->isClient()) {
            return response()->json(['error' => "L'utilisateur n'est pas un client valide."], 403);
        }
    
        DB::beginTransaction();
    
        try {
            $montantTotal = 0;

        foreach ($request->produits as $produit) {
            $produitModel = Produit::find($produit['produit_id']);
            if ($produitModel) {
                $montantTotal += $produitModel->prix * $produit['quantite'];
            } else {
                DB::rollBack();
                return response()->json(['error' => "Produit ID {$produit['produit_id']} non trouvé."], 404);
            }
        }

    
            $dette = Dette::create([
                'user_id' => $user->id,
                'montant_total' => $montantTotal,
                'montant_paye' => 0,
                'est_credible' => true,
                'date_dette' => now(),
                'statut' => Dette::STATUT_EN_COURS, 
            ]);
    
            foreach ($request->produits as $produit) {
                $dette->produits()->attach($produit['produit_id'], ['quantite' => $produit['quantite']]);
            }
    
            DB::commit();

    
            return response()->json(new DetteRessource($dette), 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'enregistrement de la dette', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de l\'enregistrement de la dette : ' . $e->getMessage()], 500);
        }
    }
    


    public function payer(PayerDetteRequest $request)
{
    DB::beginTransaction();

    try {
        $dette = Dette::findOrFail($request->dette_id);

        $dette->montant_paye += $request->montant;
        $dette->save();

        $montant_restant = $dette->montant_total - $dette->montant_paye;

        if ($dette->montant_paye >= $dette->montant_total) {
            $dette->statut = 'régularisée';
        } else {
            $dette->statut = 'partiellement réglée';
        }
        Log::info('Début du paiement', ['dette_id' => $request->dette_id, 'montant' => $request->montant]);

        $dette->save();

        DB::commit();

        return response()->json([
            'dette' => new DetteRessource($dette),
            'montant_restant' => $montant_restant
        ], 200);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Erreur lors du paiement de la dette : ' . $e->getMessage()], 500);
    }
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
