<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AidProgram extends Model
{
    use HasFactory;

    protected $fillable = ['aid_program_name', 'description', 'program_type_id', 'background_image', 'default_background'];

    public function programType()
    {
        return $this->belongsTo(ProgramType::class, 'program_type_id');
    }
}
