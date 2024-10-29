<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'nom'=> $this->nom,
            'prenom'=> $this->prenom,
            'email'=> $this->email,
            'password'=> $this->password,
            'telephone'=> $this->telephone,
            'adresse'=> $this->adresse,
            'role'=> $this->role ?  $this->role->libelle : null,
            'isActive'=> $this->isActive,
            'created_at' => $this->created_at->format('d-m-y H:i:s'),
            'updated_at' => $this->updated_at->format('d-m-y H:i:s'),
        ];
    }
}
