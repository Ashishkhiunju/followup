<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommender extends Model
{
    use HasFactory;
    protected $fillable = ['name','address','phone','email','company_name','citizen_ship_no','image'];
}
