<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dette extends Model
{
    use HasFactory;
     

    protected $guarded=['id'];

    const STATUT_EN_COURS = 'en cours';
    const STATUT_REGLE = 'règlement effectué';
    const STATUT_IMPAYE = 'impayé';


    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'dette_produits')
                    ->withPivot('quantite')
                    ->withTimestamps();
    }


    public function calculerMontantTotal()
    {
        $total = 0;
        foreach ($this->produits as $produit) {
            $total += $produit->prix * $produit->pivot->quantite;
        }
        return $total;
    }

    public function mettreAJourMontantTotal(): void
    {
        $this->montant_total = $this->calculerMontantTotal();
        $this->save();
    }   

    public function montantPaye(): float
    {
        return $this->montant_paye ?? 0;
    }

    public function estCredible(): bool
    {
        return $this->est_credible;
    }
}
