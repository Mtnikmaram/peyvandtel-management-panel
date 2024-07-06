<?php

namespace App\VoiceFileHelpers;

use Exception;
use Illuminate\Support\Facades\File;

final class VoiceFileHelper
{
    public function __construct(private string $filePath)
    {
        if (!File::isReadable($this->filePath))
            throw new Exception("can not read the file");
    }

    public function getDuration(): ?int
    {
        $extension = File::guessExtension($this->filePath);

        return match ($extension) {
            default => throw new Exception("wrong file extension ($extension). required mp3 or wav"),
            "mp3" => (new MP3File($this->filePath))->getDuration(),
            "wav" => (new WavFile($this->filePath))->getDuration(),
        };
    }
}
