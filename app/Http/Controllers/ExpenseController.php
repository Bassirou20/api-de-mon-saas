<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $depenses = Expense::all();
        return response()->json($depenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'libelle' => 'required|string|max:255',
            'montant' => 'required|numeric',
            'categorie' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
    
        $depense = new Expense($validatedData);
        $depense->user_id = Auth::user() ? Auth::user()->id : null;
        $depense->date_depense = now(); 
        $depense->save();
    
        return response()->json(['message' => 'Dépense créée avec succès!', 'depense' => $depense]);
    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $depense = Expense::findOrFail($id);
        return response()->json($depense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'libelle' => 'nullable|string|max:255',
            'montant' => 'nullable|numeric',
            'date_depense' => 'nullable|date',
            'categorie' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $depense = Expense::findOrFail($id);
        $depense->update($request->all());

        return response()->json($depense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $depense = Expense::findOrFail($id);
        $depense->delete();

        return response()->json(['message' => 'Dépense supprimée avec succès']);
    }
}
