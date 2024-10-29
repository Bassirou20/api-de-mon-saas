<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandeRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numero_commande' => $this->numero_commande,
            'date_commande' => $this->date_commande,
            'client' => new UserRessource($this->whenLoaded('client')),
            'livreur' => new UserRessource($this->whenLoaded('livreur')),
            'adresse_livraison' => $this->adresse_livraison,
            'date_livraison' => $this->date_livraison,
            'produits' => ProduitResource::collection($this->whenLoaded('produits')),
            'total' => $this->total,
            'statut' => $this->statut,
            'mode_paiement' => $this->mode_paiement,
            'confirmation_paiement' => $this->confirmation_paiement,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }   
}
