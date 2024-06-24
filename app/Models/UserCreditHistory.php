<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCreditHistory extends Model
{
    use HasFactory;

    /*=================================== Model Properties ====================================*/
    /**
     * @var array<int, string>
     */
    protected $appends = [
        'old_credit',
        'created_jal',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'is_increase',
        'amount',
        'updated_credit',
        'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_increase" => "bool"
    ];

    /*=================================== Model Attributes ====================================*/
    public function getOldCreditAttribute()
    {
        $amount = $this->is_increase ? $this->amount : -1 * $this->amount;
        return $this->updated_credit + $amount;
    }

    /*=================================== Relationship ====================================*/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
