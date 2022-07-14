<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanContact extends Model
{
    use HasFactory;
    protected $table= 'loans_contacts';
    protected $fillable = [
        'loan_id',
        'installation_date',
        'contacted',
        'paid',
    ];

    public function loan(){
        return $this->belongsTo(Loan::class,'loan_id');
    }
}
