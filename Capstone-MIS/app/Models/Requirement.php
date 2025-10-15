<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    protected $fillable = ['document_requirement'];

    public function aidPrograms()
    {
        return $this->belongsToMany(AidProgram::class, 'aid_program_requirement');
    }
}
