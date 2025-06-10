<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alternative extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'case_id', 'user_id'];

    public function case()
    {
        return $this->belongsTo(Cases::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function criteriaValues()
    {
        return $this->hasMany(CriteriaValue::class);
    }

    // Get specific criteria value for this alternative
    public function getCriteriaValue($criteriaId)
    {
        $criteriaValue = $this->criteriaValues->where('criteria_id', $criteriaId)->first();
        return $criteriaValue ? $criteriaValue->value : 0;
    }

    // Check if alternative has specific criteria
    public function hasCriteria($criteriaId)
    {
        return $this->criteriaValues->where('criteria_id', $criteriaId)->isNotEmpty();
    }

    // Add or update criteria value
    public function setCriteriaValue($criteriaId, $value)
    {
        return $this->criteriaValues()->updateOrCreate(
            ['criteria_id' => $criteriaId],
            ['value' => $value]
        );
    }

    // Get all criteria IDs associated with this alternative
    public function getCriteriaIds()
    {
        return $this->criteriaValues->pluck('criteria_id')->toArray();
    }
}