<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    use HasFactory;

    protected $table = 'criteria';

    protected $fillable = [
        'name', 
        'weight', 
        'type', 
        'description', 
        'preference_function', 
        'p', 
        'q',
        'case_id'
    ];

    // Add constants for preference functions
    const PREFERENCE_USUAL = 'usual';
    const PREFERENCE_QUASI = 'quasi';
    const PREFERENCE_LINEAR = 'linear';
    const PREFERENCE_LEVEL = 'level';
    const PREFERENCE_LINEAR_QUASI = 'linear_quasi';
    const PREFERENCE_GAUSSIAN = 'gaussian';

    public static function preferenceFunctions()
    {
        return [
            self::PREFERENCE_USUAL => 'Usual Criterion',
            self::PREFERENCE_QUASI => 'Quasi Criterion (U-shape)',
            self::PREFERENCE_LINEAR => 'Linear Criterion (V-shape)',
            self::PREFERENCE_LEVEL => 'Level Criterion',
            self::PREFERENCE_LINEAR_QUASI => 'Linear Criterion with Indifference Area',
            self::PREFERENCE_GAUSSIAN => 'Gaussian Criterion',
        ];
    }

    public function values()
    {
        return $this->hasMany(CriteriaValue::class);
    }

    public function isBenefit()
    {
        return $this->type === 'benefit';
    }
}