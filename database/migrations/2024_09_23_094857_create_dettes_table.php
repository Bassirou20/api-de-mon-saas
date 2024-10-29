<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dettes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('montant_total', 10, 2); 
            $table->decimal('montant_paye', 10, 2)->default(0); 
            $table->enum('statut', [
                'en cours', 
                'régularisée', 
                'partiellement réglée', 
                'annulée', 
            ])->default('en cours');
            $table->boolean('est_credible')->default(true);
            $table->timestamp('date_dette')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dettes');
    }
};
