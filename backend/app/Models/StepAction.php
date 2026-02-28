<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StepAction extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'request_step_id',
        'user_id',
        'action',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships
    public function requestStep()
    {
        return $this->belongsTo(RequestStep::class, 'request_step_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }
}
