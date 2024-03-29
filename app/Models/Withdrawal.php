<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $table = "withdrawals";

    protected $fillable = ['completed', 'ref_debt_id'];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function prepaid()
    {
        return $this->belongsTo(Person::class, 'prepaid_person', 'person_id');
    }
}