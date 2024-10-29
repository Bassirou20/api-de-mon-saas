<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetteRequest extends FormRequest
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
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'produits' => 'required|array',
            'produits.*.produit_id' => 'required|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
            // 'montant_total' => 'required|numeric|min:0', 
            'montant_paye' => 'nullable|numeric|min:0', 
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Le client est requis.',
            'produits.required' => 'Les produits sont requis.',
            'produits.*.produit_id.required' => 'L\'identifiant du produit est requis.',
            'produits.*.quantite.required' => 'La quantité est requise.',
            // 'montant_total.required' => 'Le montant total est requis.',
            'montant_paye.numeric' => 'Le montant payé doit être un nombre.',
        ];
    }
}
