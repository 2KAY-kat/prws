<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finding extends Model
{
    protected $fillable = ['audit_id', 'rule_id', 'name', 'category', 'severity', 'points_available', 'points_earned', 'passed'];
    protected $casts = ['passed' => 'boolean'];
}