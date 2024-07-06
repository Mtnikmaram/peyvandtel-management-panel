<?php

namespace App\VoiceFileHelpers;

use Exception;
use getID3;

class MP3File implements VoiceFileInterface
{
    private $filePath;
    private $getID3;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->getID3 = new getID3;
    }

    public function getDuration():int
    {
        $fileInfo = $this->getID3->analyze($this->filePath);
        if (isset($fileInfo['playtime_seconds'])) {
            return intval(ceil($fileInfo['playtime_seconds']));
        } else {
            throw new Exception('Unable to determine the duration of the MP3 file.');
        }
    }
}
