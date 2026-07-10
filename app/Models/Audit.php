<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = ['url', 'score', 'certification'];
    public function findings() { return $this->hasMany(Finding::class); }
}
