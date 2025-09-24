<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'aid_program_id',
        'barangay_ids',
        'beneficiary_type',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'barangay_ids' => 'array', // Automatically cast JSON to array
    ];

    public function aidProgram()
    {
        return $this->belongsTo(AidProgram::class, 'aid_program_id');
    }
}
