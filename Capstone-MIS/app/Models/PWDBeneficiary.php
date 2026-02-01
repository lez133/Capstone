<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class PWDBeneficiary extends Model
{
    use HasFactory;

    protected $table = 'pwd_beneficiaries';

    protected $fillable = [
        'barangay_id',
        'last_name',
        'first_name',
        'middle_name',
        'gender',
        'type_of_disability',
        'pwd_id_number',
        'remarks',
        'birthday',
        'age',
        'valid_from',
        'valid_to',
        'validity_years',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function setPwdIdNumberAttribute($value)
    {
        $this->attributes['pwd_id_number'] = Crypt::encryptString($value);
    }

    public function getPwdIdNumberAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    protected static function booted()
    {
        static::creating(function ($beneficiary) {
            $years = (int) ($beneficiary->validity_years ?? 5);
            $validFrom = Carbon::now();
            $validTo = $validFrom->copy()->addYears($years);

            $beneficiary->valid_from = $validFrom->toDateString();
            $beneficiary->valid_to = $validTo->toDateString();
        });
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::now()->greaterThan(Carbon::parse($this->valid_to));
    }
}

