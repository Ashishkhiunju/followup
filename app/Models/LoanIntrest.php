<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanIntrest extends Model
{
    use HasFactory;
    protected $table = 'loan_intrest';
    protected $fillable = ['loan_id','date','intrest_amount'];
}
