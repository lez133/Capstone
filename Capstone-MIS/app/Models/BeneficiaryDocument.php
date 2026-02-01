<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BeneficiaryDocument extends Model
{
    protected $table = 'beneficiary_documents';

    protected $fillable = [
        'beneficiary_id',
        'document_type',
        'aid_type',
        'description',
        'file_path',
        'status',
        'uploaded_at',
        'active',
        'assisted_by',
    ];

    /**
     * CRITICAL: Ensure datetime fields are cast to Carbon instances
     */
    protected $casts = [
        'uploaded_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'active' => 'boolean',
        'beneficiary_id' => 'integer',
        'assisted_by' => 'integer',
    ];

    /**
     * Safe accessor for formatted upload date
     */
    public function getFormattedUploadedAtAttribute(): string
    {
        if (!$this->uploaded_at) {
            return 'N/A';
        }

        $date = $this->uploaded_at;
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format('M d, Y');
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    public function aidProgram()
    {
        return $this->belongsTo(\App\Models\AidProgram::class, 'aid_program_id');
    }
}
