<?php

namespace App\VoiceFileHelpers;

use Exception;

class WavFile implements VoiceFileInterface
{
    public function __construct(private string $file)
    {
        if (!is_readable($file))
            throw new Exception("can not read the file");
    }

    public function getDuration(): ?int
    {
        $sec = null;
        $fp = fopen($this->file, 'r');
        if (fread($fp, 4) == "RIFF") {
            fseek($fp, 20);
            $rawheader = fread($fp, 16);
            $header = unpack('vtype/vchannels/Vsamplerate/Vbytespersec/valignment/vbits', $rawheader);
            $pos = ftell($fp);
            while (fread($fp, 4) != "data" && !feof($fp)) {
                $pos++;
                fseek($fp, $pos);
            }
            $rawheader = fread($fp, 4);
            $data = unpack('Vdatasize', $rawheader);
            $sec = $data['datasize'] / $header['bytespersec'];
        }

        fclose($fp);
        return $sec;
    }
}
