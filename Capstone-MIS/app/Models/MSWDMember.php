<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Barangay;

class MSWDMember extends Authenticatable
{
    use HasFactory;

    // Explicitly specify the table name
    protected $table = 'mswd_members';

    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'birthday',
        'gender',
        'role',
        'email',
        'contact',
        'username',
        'password',
        'profile_picture',
        'created_by',
        'barangay_id',
    ];

    /**
     * Full name accessor (handles different field names)
     */
    public function getFullNameAttribute()
    {
        $first = $this->fname ?? $this->first_name ?? $this->given_name ?? '';
        $middle = $this->mname ?? '';
        $last = $this->lname ?? $this->last_name ?? '';
        $parts = array_filter([trim($first), trim($middle), trim($last)]);
        return trim(implode(' ', $parts));
    }

    // Relationship to the user who created the member
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function scopeBrgyRepresentatives($query)
    {
        return $query->where('role', 'brgy_representative');
    }
}
