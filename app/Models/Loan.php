<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
   
    protected $fillable = ['customer_id','loan_type','loan_amount','loan_duration','loan_purpose','installation_type','recommend_to','issue_date','due_date'];


    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function loan_details(){
        return $this->hasMany(LoanDetail::class,'loan_id');
    }
}

