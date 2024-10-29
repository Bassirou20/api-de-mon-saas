<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProduitRequest extends FormRequest
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
            'libelle' => 'required|string|max:255',
            'quantite' => 'required',
            'prix' => 'required',
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'categorie_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ];
    }


    public function messages()
    {
        return [
            'libelle.required' => 'Le libellé est requis',
            'quantite.required' => 'La quantité est requise',
            'prix.required' => 'Le prix est requis',
            'fournisseur_id.required' => 'Le fournisseur est requis',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné est invalide',
            'image.image' => 'Le fichier doit être une image',
        ];
    }
}
