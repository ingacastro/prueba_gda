<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    use HasFactory;

    protected $fillable = ['id_com', 'id_reg', 'description',  'status'];

    public function customer(){
        return $this->hasOne('App\Models\Customer');
    }
}
