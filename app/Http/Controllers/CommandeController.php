<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommandeRequest;
use App\Http\Resources\CommandeRessource;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $commandes = Commande::with(['produits' => function ($query) {
                $query->withPivot('quantite');
            }, 'client', 'livreur'])->get();
    
            return response()->json(CommandeRessource::collection($commandes), 200);
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
    public function store(CommandeRequest $request)
{
    DB::beginTransaction();
    
    try {
        Log::info('Données de la requête : ', $request->all());

        // Validation des données
        $validatedData = $request->validated();
        $total = $this->calculateTotal($request);

        // Création des données de commande
        $commandeData = array_merge($validatedData, [
            'date_commande' => now(),
            'total' => $total,
        ]);

        // Ajout de l'utilisateur à la commande si spécifié
        if ($request->has('client_id')) {
            $commandeData['user_id'] = $request->input('client_id');
        }

        // Créer la commande
        $commande = Commande::create($commandeData);

        // Vérification des produits
        // Vérification des produits
$produits = $request->input('produits');
foreach ($produits as $produit) {
    if (!isset($produit['produit_id']) || !isset($produit['quantite'])) {
        DB::rollBack();
        return response()->json(['message' => 'Identifiant ou quantité de produit manquant'], 400);
    }

    // Récupérer le produit
    $produitModel = Produit::find($produit['produit_id']);
    
    if (!$produitModel) {
        DB::rollBack();
        return response()->json(['message' => 'Produit introuvable avec l\'ID ' . $produit['produit_id']], 404);
    }

    // Vérifiez la disponibilité du produit
    if ($produitModel->quantite < $produit['quantite']) {
        DB::rollBack();
        return response()->json(['message' => 'Quantité insuffisante pour le produit ' . $produitModel->libelle], 400);
    }

    // Réduire la quantité du produit
    $produitModel->quantite -= $produit['quantite'];
    $produitModel->save();

    // Attacher le produit à la commande
    $commande->produits()->attach($produit['produit_id'], ['quantite' => $produit['quantite']]);
}


        // Vérifier et ajouter le livreur
        if ($request->has('livreur_id') && $request->filled('livreur_id')) {
            $livreur = $this->findLivreur($request->livreur_id);
        
            if (!$livreur) {
                return response()->json(['message' => 'Le livreur spécifié est introuvable ou n\'a pas le rôle de livreur'], 404);
            }
        
            if ($this->verifierDisponibiliteLivreur($livreur, $commande)) {
                DB::rollBack();
                return response()->json(['message' => 'Livreur indisponible'], 400);
            }
        
            $commande->livreur_id = $livreur->id;
            $commande->statut = 'livré';
        } else {
            if ($request->has('client_id')) {
                $commande->statut = 'récupéré';
            } else {
                // Si aucun client, laissez le statut à 'en attente'
                $commande->statut = 'en attente';
            }
        }
        
        $commande->save();
        
        DB::commit();

        // Créer un ticket lié à la commande
        $this->createTicket($commande);

        // Retourner la réponse avec les données de la commande
        return response()->json(new CommandeRessource($commande));
    } catch (\Exception $e) {
        Log::error('Erreur lors de la création de la commande : ' . $e->getMessage());
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}





private function createTicket(Commande $commande)
{
    $user = User::find($commande->user_id);

    if (!$user) {
        Log::error('Utilisateur non trouvé pour la commande : ', $commande->toArray());
        return;
    }

    $ticketData = [
        'title' => 'Commande #' . $commande->numero_commande . ' créée',
    ];

    $ticket = $user->tickets()->create($ticketData);

    $ticket->labels()->create([
        "name" => "Commande",
        "slug" => "commande",
        "is_visible" => 1
    ]);

    Log::info('Ticket créé avec succès : ', $ticket->toArray());
}

    

    /**
     * Calculer le total des produits
     */
    private function calculateTotal(Request $request): float
    {
        $total = 0;

        if ($request->has('produits')) {
            foreach ($request->input('produits') as $produit) {
                $produitModel = Produit::findOrFail($produit['produit_id']);
                $total += $produitModel->prix * $produit['quantite'];
            }
        }

        return $total;
    }

    /**
     * Attacher les produits à la commande
     */
    private function attachProduits(Commande $commande, array $produits): void
{
    foreach ($produits as $produit) {
        $commande->produits()->attach($produit['produit_id'], ['quantite' => $produit['quantite']]);
    }
    $commande->updateTotal();
}


    /**
     * Trouver un livreur par son ID et vérifier son rôle
     */
    private function findLivreur($livreurId): ?User
    {
        return User::where('id', $livreurId)
            ->whereHas('role', function ($query) {
                $query->where('libelle', 'livreur');
            })
            ->first();
    }

    /**
     * Vérifie la disponibilité du livreur
     */
    private function verifierDisponibiliteLivreur(User $livreur, Commande $nouvelleCommande): bool
    {
        // Critère 1: Maximum de commandes en cours
        if ($livreur->commandes()->where('statut', 'en cours')->count() >= 5) {
            Log::info("Livreur ID: {$livreur->id} indisponible, car il a atteint le maximum de commandes actives.");
            return true;
        }

        // Critère 2: Vérifier la distance entre les adresses
        if ($this->checkDistance($livreur, $nouvelleCommande)) {
            return true;
        }

        // Critère 3: Vérifier la disponibilité manuelle
        if (!$livreur->disponible) {
            Log::info("Livreur ID: {$livreur->id} est marqué comme indisponible.");
            return true;
        }

        Log::info("Livreur ID: {$livreur->id} est disponible pour une nouvelle commande.");
        return false;
    }

    /**
     * Vérifie la distance entre le livreur et la nouvelle commande
     */
    private function checkDistance(User $livreur, Commande $nouvelleCommande): bool
    {
        $adresseNouvelleCommande = $nouvelleCommande->adresse_livraison;
        $livraisonsActuelles = $livreur->commandes()->where('statut', 'en cours')->get();

        foreach ($livraisonsActuelles as $livraison) {
            $distance = $this->calculateDistance($livraison->adresse_livraison, $adresseNouvelleCommande);
            if ($distance > 20) { // Exemple : distance maximale de 20 km
                Log::info("Livreur ID: {$livreur->id} indisponible, distance entre les adresses supérieure à 20 km.");
                return true;
            }
        }

        return false;
    }

    /**
     * Calcul de la distance entre deux adresses avec la formule de Haversine
     */
    private function calculateDistance($adresse1, $adresse2): float
    {
        $latitudeFrom = $adresse1['latitude'];
        $longitudeFrom = $adresse1['longitude'];
        $latitudeTo = $adresse2['latitude'];
        $longitudeTo = $adresse2['longitude'];

        // Formule de Haversine
        $earthRadius = 6371; // Rayon de la Terre en kilomètres
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Retourne la distance en kilomètres
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $commande = Commande::findOrFail($id);
        return new CommandeRessource($commande);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CommandeRequest $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $commande = Commande::findOrFail($id);
            $commande->update($request->validated());
    
            return new CommandeRessource($commande);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $commande = Commande::findOrFail($id);
        $commande->delete();

        return response()->noContent();
    }


    public function annuler($id)
{
    DB::beginTransaction();

    try {
        $commande = Commande::findOrFail($id);

        if ($commande->statut === 'annulée') {
            return response()->json(['message' => 'Cette commande est déjà annulée'], 400);
        }

        if ($commande->statut === 'livré') {
            return response()->json(['message' => 'Cette commande a déjà été livrée et ne peut pas être annulée'], 400);
        }

        $commande->statut = 'annulée';
        $commande->save();

        // Réapprovisionner les produits commandés
        foreach ($commande->produits as $produit) {
            $produit->quantite += $produit->pivot->quantite;
            $produit->save();
        }

        // Confirmer la transaction
        DB::commit();

        return response()->json(['message' => 'Commande annulée avec succès', 'commande' => new CommandeRessource($commande)], 200);
    } catch (\Exception $e) {
        // En cas d'erreur, rollback
        DB::rollBack();

        Log::error('Erreur lors de l\'annulation de la commande : ' . $e->getMessage());
        return response()->json(['error' => 'Erreur lors de l\'annulation de la commande'], 500);
    }
}

}
