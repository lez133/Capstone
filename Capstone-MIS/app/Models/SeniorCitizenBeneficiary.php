<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeniorCitizenBeneficiary extends Model
{
    use HasFactory;

    protected $table = 'senior_citizen_beneficiaries';

    protected $fillable = [
        'barangay_id',
        'last_name',
        'first_name',
        'middle_name',
        'birthday',
        'age',
        'gender',
        'civil_status',
        'osca_number',
        'date_issued',
        'remarks',
        'national_id',
        'pkn',
        'rrn',
    ];

    /**
     * Define the relationship to the Barangay model.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}
