<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'id'=>$this->id,
        'libelle'=>$this->libelle,
        'description'=>$this->description,
        'prix'=>$this->prix,
        'quantite'=>$this->quantite,
        'image'=> $this->image ? asset($this->image) : null,
        'fournisseur' => new FournisseurResource($this->whenLoaded('fournisseur')),
        'categorie'=>$this->categorie ?  $this->categorie->libelle : null,
        'date_ajout'=>$this->created_at->format('d-m-y'),
        'updated_at'=>$this->updated_at->format('d-m-y H:i:s'),
        'quantity' => isset($this->pivot) ? $this->pivot->quantite : 0,
        'statut'=>$this->statut,
            
        ];
    }
}
