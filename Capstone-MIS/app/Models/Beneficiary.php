<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Beneficiary extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'barangay_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'email',
        'phone',
        'username',
        'beneficiary_type',
        'birthday',
        'age',
        'gender',
        'civil_status',
        'osca_number',
        'pwd_id',
        'password',
        'verified',
    ];

    /**
     * Define the relationship to the Barangay model.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}
