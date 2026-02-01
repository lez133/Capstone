<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AidProgramRequirementBeneficiary extends Model
{
    use HasFactory;

    protected $table = 'aid_program_requirement_beneficiary';

    protected $fillable = [
        'beneficiary_id',
        'barangay_id',
        'aid_program_id',
        'requirement_id',
        'beneficiary_document_id',
        'status',
        'remarks',
        'validated_at',
        'assisted_by',
    ];

    protected $dates = [
        'validated_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'assisted_by' => 'integer',
    ];

    // Relationships
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function aidProgram()
    {
        return $this->belongsTo(AidProgram::class);
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function beneficiaryDocument()
    {
        return $this->belongsTo(BeneficiaryDocument::class);
    }

    // relation to MSWD member who assisted
    public function assistedBy()
    {
        return $this->belongsTo(\App\Models\MSWDMember::class, 'assisted_by');
    }

    // convenience accessor
    public function getAssistedByNameAttribute(): ?string
    {
        return $this->assistedBy ? ($this->assistedBy->full_name ?? $this->assistedBy->name ?? null) : null;
    }
}
