<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingImage extends Model
{
    use HasFactory;
    protected $fillable =['saving_id','image'];
}
