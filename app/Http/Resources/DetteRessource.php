<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetteRessource extends JsonResource
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
            'client' => new UserRessource($this->client),
            'montant_total' => $this->montant_total,
            'montant_paye' => $this->montant_paye,
            'statut' => $this->statut,
            'est_credible' => $this->est_credible,
            'date_dette' => $this->date_dette,
            'produits' => $this->produits, 
        ];
    }
}
