<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchQuery extends Model
{
    protected $fillable = ['user_id', 'query', 'results_count', 'source'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
