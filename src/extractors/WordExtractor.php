<?php

namespace venveo\documentsearch\extractors;


use venveo\documentsearch\helpers\ZipHelper;

class WordExtractor extends Extractor
{
    public static function extractText($filepath): string
    {
        if (ZipHelper::isZipFile($filepath)) {
            $text = static::extractContentFromDocx($filepath);
        } else {
            $text = static::extractContentFromDoc($filepath);
        }
        return $text;
    }


    /**
     * Quick method for extracting text from a word 2007+ document.
     * @param $filepath
     * @return bool|string
     */
    protected static function extractContentFromDocx($filepath): string
    {
        $response = '';
        $xmlFilename = 'word/document.xml';
        $zip = new \ZipArchive();

        if (true === $zip->open($filepath)) {
            $xmlIndex = $zip->locateName($xmlFilename);
            if ($xmlIndex !== false) {
                $xml_data = $zip->getFromIndex($xmlIndex);
                //process data to retain line breaks between sections of text and remove all other tags.
                $response = str_replace('</w:r></w:p></w:tc><w:tc>', ' ', $xml_data);
                $response = str_replace('</w:r></w:p>', "\r\n", $response);
                $response = strip_tags($response);
            }
            $zip->close();
        }

        if (empty($response)) {
            $response = '';
        }

        return $response;
    }

    /**
     * Quick method for extracting text from a word 97 ".doc" document.
     * Only grabs text from the main document. Does not include headers, notes or footnotes.
     * @author Adapted from doc2txt by gouravmehta - https://www.phpclasses.org/package/7934-PHP-Convert-MS-Word-Docx-files-to-text.html
     * @author Adapted from Q/A by M Khalid Junaid - https://stackoverflow.com/questions/19503653/how-to-extract-text-from-word-file-doc-docx-xlsx-pptx-php
     * @see https://docs.microsoft.com/en-us/openspecs/office_file_formats/ms-doc/ccd7b486-7881-484c-a137-51170af7cc22
     * @param $filepath
     * @return string
     */
    protected static function extractContentFromDoc($filepath): string
    {
        $fileHandle = fopen($filepath, 'r');
        $line = @fread($fileHandle, filesize($filepath));
        //Break document apart using paragraph markers.
        $lines = explode(chr(0x0D), $line);
        $response = '';

        foreach ($lines as $currentLine) {

            $pos = strpos($currentLine, chr(0x00));

            if ($pos === false && $currentLine != '') {
                $response .= $currentLine . ' ';
            }
        }

        $response = preg_replace('/[^a-zA-Z0-9\s,.\-\n\r\t@\/_()]/', '', $response);

        //Technique pulls text in on first line. Subsequent lines are noise.
        $nl = stripos($response, "\n");
        if ($nl) {
            $response = substr($response, 0, $nl);
        }
        return $response;
    }
}