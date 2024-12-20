<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FournisseurRequest extends FormRequest
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
            'nom'=>'required|max:256|unique:fournisseurs',
            'adresse'=>'required',
            'contact'=>'required|unique:fournisseurs',
            'description'=>'nullable|max:400',
            'image'=>'nullable'
        ];
    }


    public function messages()
    {
        return [
            'nom.unique'=>'ce fournisseur existe déjà',
            'contact.unique'=>'ce contact existe déjà',
            // 'image.image' => 'Le fichier doit être une image',
        ];
    }
}
