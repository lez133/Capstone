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
        'published',
    ];

    protected $casts = [
        'barangay_ids' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'published' => 'boolean',
    ];

    public function aidProgram()
    {
        return $this->belongsTo(AidProgram::class, 'aid_program_id');
    }
}
