<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'installation_date',
        'reminder_date_eng',
        'reminder_date_nep',
        'reminder_detail',
    ];

    public function loan(){
        return $this->belongsTo(Loan::class,'loan_id');
    }
}
