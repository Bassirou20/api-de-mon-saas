<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
                    'nom' => 'required|string|max:255',
                    'prenom' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8',
                    'telephone' => 'required|string|unique:users',
                    'adresse' => 'nullable|string',
                    'role_id' => 'required|exists:roles,id',
                    // 'isActive' => 'nullable',
                ];
    }



    public function messages()
{
    return [
        'nom.required' => 'Le nom est obligatoire.',
        'prenom.required' => 'Le prenom est obligatoire.',
        'email.required' => "L'email est obligatoire.",
        'email.unique' => "L'email existe déjà.",
        'email.unique' => "Cet adresse email est déjà utilisé.",
        'telephone.required' => "Le numéro de téléphone est requis.",
        'telephone.unique' => "Ce numéro de téléphone existe déjà.",
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
    ];
}

   
}
