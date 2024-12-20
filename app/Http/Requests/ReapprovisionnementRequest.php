<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReapprovisionnementRequest extends FormRequest
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
            'produits' => 'required|array',
            'produits.*.id' => 'required|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'produits.required' => 'Veuillez fournir les produits à réapprovisionner.',
            'produits.*.id.required' => 'L\'identifiant du produit est requis.',
            'produits.*.id.exists' => 'L\'identifiant du produit est invalide.',
            'produits.*.quantite.required' => 'La quantité est requise.',
            'produits.*.quantite.integer' => 'La quantité doit être un nombre entier.',
            'produits.*.quantite.min' => 'La quantité doit être au moins de 1.',
        ];
    }
}
