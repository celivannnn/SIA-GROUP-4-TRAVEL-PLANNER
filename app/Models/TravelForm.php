<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelForm extends Model
{
    protected $table = 'travel_form';

    protected $fillable = [
        'name',
        'country',
        'travel_date',
        'budget',
        'days',
        'currency',
        'user_id',  // Important for linking to user
    ];

    // Each travel form belongs to one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
