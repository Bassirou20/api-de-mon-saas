<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commande extends Model
{
    use HasFactory;

    protected $guarded=['id'];


    public function client():BelongsTo{
        return $this->belongsTo(User::class,'user_id');
    }

    public function livreur():BelongsTo{
        return $this->belongsTo(User::class,'livreur_id');
    }


    public function produits()
    {
        return $this->belongsToMany(Produit::class,'commande_preoduits')->withPivot('quantite');
    }

    public function updateTotal()
    {
        if ($this->produits->isNotEmpty()) {
            $total = $this->produits->reduce(function ($carry, $produit) {
                return $carry + ($produit->pivot->quantite * $produit->prix);
            }, 0);
    
            $this->total = $total;
            $this->save();
        }
    }


    public static function boot()
    {
        parent::boot();

        static::creating(function ($commande) {
            $date = Carbon::now()->format('Ymd'); 
            $lastOrderOfDay = Commande::whereDate('created_at', Carbon::today())->count();
            $increment = str_pad($lastOrderOfDay + 1, 3, '0', STR_PAD_LEFT); 

            $commande->numero_commande = 'CMD-' . $date . '-' . $increment;
        });
    }
}
