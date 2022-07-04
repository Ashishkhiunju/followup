<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
   
    protected $fillable = ['name','address','phone','email','company_name','loan_type','loan_amount','loan_duration','image','loan_purpose','installation_type','recommend_to','issue_date','due_date'];
}
