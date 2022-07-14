<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class LoanInstallationDate extends Model
{
    use HasFactory;
    protected $fillable = ['loan_id','next_installation_eng_date','next_installation_nep_date'];



    // public function available_auth_loans(){
    //     return $this->belongsTo(Loan::class,'loan_id');
    // }

    public function loan(){
        return $this->belongsTo(Loan::class,'loan_id');
        // return $this->available_auth_loans()->where('user_id','=', 1);
    }
}
