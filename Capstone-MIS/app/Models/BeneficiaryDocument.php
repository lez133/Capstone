<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiaryDocument extends Model
{
    protected $fillable = [
        'beneficiary_id',
        'document_type',
        'file_path',
        'status',
        'uploaded_at',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
