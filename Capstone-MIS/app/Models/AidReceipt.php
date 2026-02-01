<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AidReceipt extends Model
{
    protected $table = 'aid_receipts';

    protected $fillable = [
        'beneficiary_id',
        'aid_program_id',
        'schedule_id',
        'receipt_date',
        'notes',
        'confirmed_by',
    ];

    protected $dates = ['receipt_date'];

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function aidProgram(): BelongsTo
    {
        return $this->belongsTo(AidProgram::class);
    }
}
