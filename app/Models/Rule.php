<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = ['rule_id', 'name', 'category', 'severity', 'points', 'active'];
    protected $casts = ['active' => 'boolean'];
}
