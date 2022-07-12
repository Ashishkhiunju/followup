<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanInstallationDate extends Model
{
    use HasFactory;
    protected $fillable = ['loan_id','next_installation_eng_date','next_installation_nep_date'];

    public function loan(){
        return $this->belongsTo(Loan::class,'loan_id');
    }
}
