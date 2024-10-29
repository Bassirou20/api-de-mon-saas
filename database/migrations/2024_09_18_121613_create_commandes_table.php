<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_commande')->unique();
            $table->timestamp('date_commande');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade') ;
            $table->unsignedBigInteger('livreur_id')->nullable();
            $table->foreign('livreur_id')->references('id')->on('users')->onDelete('set null') ;
            $table->enum('statut',['en attente','en cours','annulée','livré','récupéré'])->default('en attente');
            $table->enum('mode_paiement',['en espèce','Wave','Orange-Money','livré','carte','paypal'])->default('en espèce')->nullable();
            $table->text('adresse_livraison')->nullable();
            $table->date('date_livraison')->nullable();
            $table->decimal('total',10,2);
            $table->boolean('confirmation_paiement')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
