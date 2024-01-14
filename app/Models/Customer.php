<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Instancio la tabla 'customer' 
    protected $table = 'customers';

    public $timestamps = false;

    protected $fillable = ['dni', 'id_reg', 'id_com', 'email', 'name', 'last_name',
        'address', 'date_reg', 'status'];

    public function commune(){
        return $this->belongsTo('App\Models\Commune', 'id_com', 'id_com');
    }

    public function region(){
        return $this->belongsTo('App\Models\Region', 'id_reg', 'id_reg');
    }
}
