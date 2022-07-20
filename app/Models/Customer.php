<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = ['name','address','phone','email','company_name','citizen_ship_no','image'];

    public function loans(){
        return $this->hasMany(Loan::class,'customer_id');
    }
}
