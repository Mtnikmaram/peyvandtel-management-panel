<?php

namespace App\Models;

use App\Observers\UserCreditHistoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy([UserCreditHistoryObserver::class])]
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
    protected function oldCredit(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["updated_credit"] + ($attributes["is_increase"] ? $attributes["amount"] : -1 * $attributes["amount"]),
        );
    }

    protected function createdJal(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => jdate($attributes["created_at"])->format("Y-m-d H:i"),
        );
    }

    /*=================================== Relationship ====================================*/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
