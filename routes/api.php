<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DetteController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::apiResource('user',UserController::class);
Route::apiResource('categorie',CategorieController::class);
Route::apiResource('role',RoleController::class);
Route::apiResource('fournisseur',FournisseurController::class);
Route::apiResource('produit',ProduitController::class);
Route::apiResource('commande',CommandeController::class);
Route::apiResource('dette',DetteController::class);
Route::apiResource('depenses',ExpenseController::class);
Route::post('/dettes/payerdette', [DetteController::class, 'payer']);
Route::get('/users/clients', [UserController::class, 'getClients']);
Route::get('/users/livreurs', [UserController::class, 'getLivreurs']);
Route::put('/commandes/{commande}/annuler', [CommandeController::class, 'annuler'])->name('commandes.annuler');
Route::patch('/produits/reapprovisionner', [ProduitController::class, 'reapprovisionner']);
Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleActiveStatus']);
Route::post('send-email', [EmailController::class, 'sendEmail']);






