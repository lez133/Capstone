<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];

    public function getFullNameAttribute()
    {
        return trim("{$this->fname} {$this->mname} {$this->lname}");
    }

    // Relationship to the user who created the member
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBrgyRepresentatives($query)
    {
        return $query->where('role', 'brgy_representative');
    }
}
