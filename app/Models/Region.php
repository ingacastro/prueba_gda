<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['id_reg', 'description', 'status'];

    public function commune(){
        return $this->hasOne('App\Models\Commune');
    }
}
