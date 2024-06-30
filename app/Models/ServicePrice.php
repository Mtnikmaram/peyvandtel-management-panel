<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="ServicePrice",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1",
 *   ),
 *   @OA\Property(
 *     property="service_id",
 *     type="string",
 *     example="SERVICE_ID",
 *   ),
 *   @OA\Property(
 *     property="amount",
 *     type="integer",
 *     example="100",
 *   ),
 *   @OA\Property(
 *     property="setting",
 *     type="array",
 *     @OA\Items(
 *       @OA\Property(
 *         type="object",
 *         @OA\Property(
 *           property="key",
 *           type="string",
 *           example="value",
 *         )
 *       )
 *     )
 *   ),
 * )
 */
class ServicePrice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'amount',
        'setting',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'setting' => 'array',
        ];
    }

    /*=================================== Relationship ====================================*/
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
