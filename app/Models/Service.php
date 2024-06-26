<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @OA\Schema(
 *   schema="Service",
 *   @OA\Property(
 *     property="id",
 *     type="string",
 *     example="SAHAB_PARTAI_SPEACH_TO_TEXT"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="آوانگار (تبدیل گفتار به متن) Speech To Text"
 *   ),
 *   @OA\Property(
 *     property="is_active",
 *     type="boolean",
 *     example="false"
 *   ),
 *   @OA\Property(
 *     property="has_credential",
 *     type="boolean",
 *     example="false"
 *   ),
 * )
 */
class Service extends Model
{
    /*=================================== Static Properties ====================================*/
    public static $services = [
        [
            "id" => "SAHAB_PARTAI_SPEACH_TO_TEXT",
            "name" => "آوانگار (تبدیل گفتار به متن) Speech To Text",
        ]
    ];

    /*=================================== Model Properties ====================================*/
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'credential',
        'active'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credential' => 'encrypted',
            'active' => 'boolean',
        ];
    }

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'is_active',
        'has_credential',
    ];

    /*=================================== Scopes ====================================*/
    /**
     * Scope a query to only include active services.
     */
    public function scopeActive(Builder $query): void
    {
        $query
            ->whereNotNull('credential')
            ->where('active', 1);
    }

    /*=================================== Model Attributes ====================================*/
    protected function credential(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => encrypt($value),
            get: fn (string $value) => decrypt($value),
        );
    }

    protected function hasCredential(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => !!$attributes["credential"],
        );
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["active"] && $attributes["credentials"],
        );
    }

    /*=================================== Relationship ====================================*/
    public function prices(): HasMany
    {
        return $this->hasMany(ServicePrice::class, 'service_id');
    }
}
