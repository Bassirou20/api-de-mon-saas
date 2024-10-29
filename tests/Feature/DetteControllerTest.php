<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Dette;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetteControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_client_peut_enregistrer_une_dette()
    {
        // Création d'un utilisateur avec le rôle client
        $client = User::factory()->create(['role_id' => config('roles.client')]);

        // Données de la requête
        $data = [
            'user_id' => $client->id,
            'montant_total' => 1000,
            'produits' => [
                ['produit_id' => 1, 'quantite' => 2],
            ],
        ];

        // Envoi de la requête
        $response = $this->postJson('/api/dettes', $data);

        // Vérification des assertions
        $response->assertStatus(201);
        $this->assertDatabaseHas('dettes', [
            'user_id' => $client->id,
            'montant_total' => 1000,
        ]);
    }

    /** @test */
    public function un_utilisateur_non_client_ne_peut_pas_enregistrer_une_dette()
    {
        // Création d'un utilisateur avec un rôle non client
        $nonClient = User::factory()->create(['role_id' => config('roles.non_client')]);

        // Données de la requête
        $data = [
            'user_id' => $nonClient->id,
            'montant_total' => 1000,
        ];

        // Envoi de la requête
        $response = $this->postJson('/api/dettes', $data);

        // Vérification des assertions
        $response->assertStatus(403);
        $this->assertDatabaseMissing('dettes', [
            'user_id' => $nonClient->id,
        ]);
    }

    /** @test */
    public function un_client_doît_avoir_un_montant_total_valide()
    {
        // Création d'un utilisateur avec le rôle client
        $client = User::factory()->create(['role_id' => config('roles.client')]);

        // Données de la requête avec montant_total manquant
        $data = [
            'user_id' => $client->id,
            'produits' => [
                ['produit_id' => 1, 'quantite' => 2],
            ],
        ];

        // Envoi de la requête
        $response = $this->postJson('/api/dettes', $data);

        // Vérification des assertions
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['montant_total']);
    }

    /** @test */
    public function le_client_doit_avoir_des_produits_valides()
    {
        // Création d'un utilisateur avec le rôle client
        $client = User::factory()->create(['role_id' => config('roles.client')]);

        // Données de la requête avec des produits invalides
        $data = [
            'user_id' => $client->id,
            'montant_total' => 1000,
            'produits' => [
                ['produit_id' => 999, 'quantite' => 2], // Produit inexistant
            ],
        ];

        // Envoi de la requête
        $response = $this->postJson('/api/dettes', $data);

        // Vérification des assertions
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['produits.0.produit_id']);
    }
}