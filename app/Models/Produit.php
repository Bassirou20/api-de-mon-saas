<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded=['id'];


    public function categorie():BelongsTo{
        return $this->belongsTo(Categorie::class);
    }

    public function fournisseur():BelongsTo{
        return $this->belongsTo(Fournisseur::class); 
    }

    public function dettes()
    {
        return $this->belongsToMany(Dette::class, 'dette_produits')
                    ->withPivot('quantite') 
                    ->withTimestamps();
    }


    public function commandes()
    {
        return $this->belongsToMany(Commande::class,'commande_preoduits')->withPivot('quantite');
    }
}
