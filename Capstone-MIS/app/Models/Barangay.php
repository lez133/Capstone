<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = ['barangay_name'];

    /**
     * Define the relationship to the SeniorCitizenBeneficiary model.
     */
    public function beneficiaries()
    {
        return $this->hasMany(SeniorCitizenBeneficiary::class, 'barangay_id');
    }
}
