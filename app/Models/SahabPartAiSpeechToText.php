<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SahabPartAiSpeechToText extends Model
{
    use HasFactory, HasUuids;

    /*=================================== Static Properties ====================================*/
    public static $statuses = ['waiting', 'processing', 'failed', 'successful'];

    /*=================================== Model Properties ====================================*/
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        "status",
        "used_credit",
        "file",
        "file_length",
        "payload",
        "result"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
        'file',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result' => 'array',
        ];
    }

    /*=================================== Model Attributes ====================================*/
    protected function filePath(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Service::getDirectoryPath() . DIRECTORY_SEPARATOR . $attributes["file"],
        );
    }


    /*=================================== Relationship ====================================*/
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
