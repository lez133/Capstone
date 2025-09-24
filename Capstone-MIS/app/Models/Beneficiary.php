<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Beneficiary extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'email',
        'phone',
        'beneficiary_type',
        'birthday',
        'gender',
        'civil_status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
