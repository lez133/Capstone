<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentenarianCashGift extends Model
{
    protected $fillable = ['beneficiary_id', 'milestone_age', 'given_at'];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }
}
