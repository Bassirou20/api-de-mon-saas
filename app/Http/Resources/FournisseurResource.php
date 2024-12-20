<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FournisseurResource extends JsonResource
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
            'nom' => $this->nom,
            'adresse' => $this->adresse,
            'contact' => $this->contact,
            'description' => $this->description,
            'image' => $this->image ? asset($this->image) : null,
        ];
    }
}
