<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = ['barangay_name'];


    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class, 'barangay_id');
    }

    public function seniorCitizenBeneficiaries()
    {
        return $this->hasMany(SeniorCitizenBeneficiary::class, 'barangay_id');
    }
}
