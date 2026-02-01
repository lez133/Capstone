<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AidProgram extends Model
{
    use HasFactory;

    protected $fillable = ['aid_program_name', 'description', 'program_type_id', 'background_image', 'default_background'];
    protected $casts = [
        'qualified_barangays' => 'array',
    ];

    public function programType()
    {
        return $this->belongsTo(ProgramType::class, 'program_type_id');
    }

    public function requirements()
    {
        return $this->belongsToMany(Requirement::class, 'aid_program_requirement', 'aid_program_id', 'requirement_id')
            ->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'aid_program_id');
    }
    public function beneficiaries()
    {
        return $this->belongsToMany(Beneficiary::class, 'aid_program_beneficiary', 'aid_program_id', 'beneficiary_id');
    }
}
