<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
        public function rules(): array
        {
            return [
            'client_id' => 'sometimes|exists:users,id',
            'livreur_id' => 'nullable|exists:users,id',
            'statut' => 'in:en attente,en cours,annulée,livré,récupéré|nullable',
            'mode_paiement' => 'in:en espèce,Wave,Orange-Money,carte,paypal|nullable',
            'adresse_livraison' => 'string|nullable',
            'date_livraison' => 'date|nullable',
            // 'total' => 'numeric|required',
            'confirmation_paiement' => 'boolean|nullable',
            'produits' => 'array|nullable',
            'produits.*.produit_id' => 'required|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
            ];


            if ($this->filled('livreur_id')) {
            $rules['livreur_id'] = 'exists:users,id';
        }
        }


    public function messages()
    {
        return [
            // 'client.required' => 'Le champ utilisateur est obligatoire.',
            'client.exists' => 'Le  client sélectionné n\'existe pas.',
            // 'livreur_id.required' => 'Le champ livreur est obligatoire.',
            'livreur_id.exists' => 'Le livreur sélectionné n\'existe pas.',
            'adresse_livraison.required' => 'L\'adresse de livraison est obligatoire.',
            // 'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
            'produits.required' => 'Vous devez ajouter au moins un produit.',
            'produits.*.produit_id.required' => 'Le produit est obligatoire.',
            'produits.*.produit_id.exists' => 'Le produit sélectionné n\'existe pas.',
            'produits.*.quantite.required' => 'La quantité est obligatoire pour chaque produit.',
            'produits.*.quantite.min' => 'La quantité minimale pour chaque produit est 1.',
        ];
    }
}
