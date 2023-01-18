<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAdjustment extends Model
{
    protected $table = "plan_adjustments";

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }
}