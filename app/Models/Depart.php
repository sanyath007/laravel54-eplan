<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depart extends Model
{
    protected $connection = "person";
    protected $table = "depart";
    protected $primaryKey = "depart_id";
    // public $increment = false;
    public $timestamps = false;

    public function faction()
    {
        return $this->belongsTo(Faction::class, 'faction_id', 'faction_id');
    }

    public function divisions()
    {
        return $this->hasMany(Division::class, 'depart_id', 'depart_id');
    }
}