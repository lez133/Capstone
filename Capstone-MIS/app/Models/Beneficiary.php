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
        'assisted_by',
        'otp_code',
        'avatar',
    ];

    // Ensure otp_created_at is cast to a datetime/Carbon instance
    protected $casts = [
        'otp_created_at' => 'datetime',
    ];

    /**
     * Define the relationship to the Barangay model.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    /**
     * Get all documents uploaded by the beneficiary.
     */
    public function documents()
    {
        return $this->hasMany(BeneficiaryDocument::class, 'beneficiary_id');
    }
}
