<?php

namespace venveo\documentsearch\helpers;

abstract class ZipHelper
{
    public static function isZipFile($filepath)
    {
        $fh = fopen($filepath, 'r');
        $bytes = fread($fh, 4);
        fclose($fh);
        //according to zip file spec, all zip files start with the same 4 bytes.
        return ('504b0304' === bin2hex($bytes));
    }
}