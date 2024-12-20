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
        $dettes = Dette::with('client', 'produits')->get();

        return response()->json(DetteRessource::collection($dettes), 200);
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

    // Vérifier si le client a des dettes non réglées
    $dettesNonReglees = Dette::where('user_id', $user->id)
        ->where('statut', '!=', Dette::STATUT_REGULARISEE) // Changez ce statut selon votre définition
        ->count();

    if ($dettesNonReglees >= 2) {
        return response()->json(['error' => "Le client a déjà deux dettes non réglées. Impossible de lui accorder une nouvelle dette."], 403);
    }

    DB::beginTransaction();

    try {
        $montantTotal = 0;

        foreach ($request->produits as $produit) {
            $produitModel = Produit::find($produit['produit_id']);
            if ($produitModel) {
                // Limiter la quantité maximale à 5
                if ($produit['quantite'] > 5) {
                    DB::rollBack();
                    return response()->json(['error' => "La quantité demandée pour le produit ID {$produit['produit_id']} ne peut pas dépasser 5."], 400);
                }

                // Vérifier la disponibilité du produit
                if ($produitModel->quantite < $produit['quantite']) {
                    DB::rollBack();
                    return response()->json(['error' => "Quantité demandée pour le produit ID {$produit['produit_id']} dépasse la quantité disponible."], 400);
                }

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

        // Notification de succès (à implémenter selon votre logique de notification)
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
        // Trouver la dette
        $dette = Dette::findOrFail($request->dette_id);
        
        // Vérifier que le montant payé ne dépasse pas le montant restant
        $montant_restant = $dette->montant_total - $dette->montant_paye;
        if ($request->montant > $montant_restant) {
            DB::rollBack();
            return response()->json(['error' => 'Le montant payé ne peut pas dépasser le montant restant.'], 400);
        }

        // Mettre à jour le montant payé
        $dette->montant_paye += $request->montant;

        // Mettre à jour le statut de la dette en fonction du montant payé
        if ($dette->montant_paye >= $dette->montant_total) {
            $dette->statut = 'régularisée';
        } else {
            $dette->statut = 'partiellement réglée';
        }

        Log::info('Début du paiement', ['dette_id' => $request->dette_id, 'montant' => $request->montant]);

        // Enregistrer les modifications
        $dette->save();

        DB::commit();

        return response()->json([
            'dette' => new DetteRessource($dette),
            'montant_restant' => $montant_restant - $request->montant // Met à jour le montant restant
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
        try {
            $produit = Dette::findOrFail($id);
            $produit->delete();

            return response()->json([
                'message' => 'Dette supprimée avec succès.',
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
