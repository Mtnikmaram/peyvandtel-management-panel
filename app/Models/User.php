<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *   schema="User",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1",
 *   ),
 *   @OA\Property(
 *     property="username",
 *     type="string",
 *     example="customer-one",
 *   ),
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="نام کامل مشتری",
 *   ),
 *   @OA\Property(
 *     property="credit",
 *     type="integer",
 *     example="1000000",
 *   ),
 *   @OA\Property(
 *     property="phone",
 *     type="string",
 *     example="09123456789",
 *   ),
 *   @OA\Property(
 *     property="credit_threshold",
 *     type="integer",
 *     example="100000",
 *   ),
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;
    /*=================================== Static Properties ====================================*/
    //

    /*=================================== Model Properties ====================================*/
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'phone',
        'password',
        'name',
        'credit_threshold',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /*=================================== Model Attributes ====================================*/
    //

    /*=================================== Relationship ====================================*/
    public function creditHistories(): HasMany
    {
        return $this->hasMany(UserCreditHistory::class, 'user_id');
    }
}
