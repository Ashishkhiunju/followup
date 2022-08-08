<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSentReminder extends Model
{
    use HasFactory;
    protected $fillable = ['loan_id','reminder_date','reminder_type'];

    public function loan(){
        return $this->belongsTo(Loan::class,'loan_id');
    }
}
