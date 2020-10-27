<?php

namespace venveo\documentsearch\extractors;


use Spatie\PdfToText\Pdf;
use venveo\documentsearch\DocumentSearch as Plugin;
use venveo\documentsearch\helpers\ZipHelper;

class ExcelExtractor extends Extractor
{
    public static function extractText($filepath): string
    {
        return static::extractContentFromXlsx($filepath);
    }


    /**
     * Extract text from a Excel 2007 file (.xlsx)
     * @param $filepath
     * @return string
     */
    protected static function extractContentFromXlsx( $filepath ): string
    {
        $xml_filename = 'xl/sharedStrings.xml'; //content file name
        $zip_handle   = new \ZipArchive();
        $response     = '';

        if (true === $zip_handle->open($filepath)) {

            if (($xml_index = $zip_handle->locateName($xml_filename)) !== false) {

                $doc = new \DOMDocument();

                //Normalize XML data.
                $xml_data   = $zip_handle->getFromIndex($xml_index);
                $doc->loadXML($xml_data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $response   = strip_tags($doc->saveXML());
            }
            $zip_handle->close();
        }
        return $response;
    }

    public static function canHandleFile($filepath): bool
    {
        return ZipHelper::isZipFile($filepath);
    }
}