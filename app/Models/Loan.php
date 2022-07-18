<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'loan_type',
        'loan_amount',
        'paid_amount',
        'remaining_amount',
        'loan_duration',
        'loan_purpose',
        'installation_type',
        'recommend_to',
        'issue_date_eng',
        'issue_date_nep',
        'due_date_eng',
        'due_date_nep',
        'user_id',
        'intrest_rate',
        'intrest_amount',
        ];


    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function loan_details(){
        return $this->hasMany(LoanDetail::class,'loan_id');
    }
    public function loan_type(){
        return $this->belongsTo(LoanType::class,'loan_type');
    }
    public function loan_contacts(){
        return $this->hasMany(LoanContact::class,'loan_id');
    }
    public function loan_reminders(){
        return $this->hasMany(LoanReminder::class,'loan_id');
    }
}

