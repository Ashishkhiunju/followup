<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'saving_type',
        'saving_amount',
        'issue_date_eng',
        'issue_date_nep',
        'user_id',
        'intrest_rate',
        'intrest_amount',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function saving_details(){
        return $this->hasMany(SavingDetail::class,'saving_id');
    }
    public function saving_withdraws(){
        return $this->hasMany(SavingWithdraw::class,'saving_id');
    }
}
