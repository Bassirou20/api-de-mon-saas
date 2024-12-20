<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateProductRequest extends FormRequest
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
            'libelle' => 'nullable|string|max:255',
            'quantite' => 'nullable|integer',
            'prix' => 'nullable|numeric',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'categorie_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'libelle.max' => 'Le libellé ne doit pas dépasser 255 caractères',
            'quantite.integer' => 'La quantité doit être un nombre entier',
            'prix.numeric' => 'Le prix doit être un nombre',
            'fournisseur_id.exists' => 'Le fournisseur sélectionné est invalide',
            'categorie_id.exists' => 'La catégorie sélectionnée est invalide',
            'image.image' => 'Le fichier doit être une image',
        ];
    }
}
