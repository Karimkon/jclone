<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'vote',
    ];

    /**
     * Get the review
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}