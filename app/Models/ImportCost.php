<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportCost extends Model
{
    protected $fillable = [
        'import_request_id',
        'item_cost', 'freight', 'insurance', 'cif', 
        'duty', 'vat', 'other_taxes', 'total_tax',
        'import_commission', 'platform_commission',
        'final_import_cost', 'breakdown'
    ];

    protected $casts = [
        'breakdown' => 'array',
    ];

    public function importRequest()
    {
        return $this->belongsTo(ImportRequest::class);
    }
}