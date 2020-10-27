<?php
namespace venveo\documentsearch\extractors;

use venveo\documentsearch\helpers\ZipHelper;

class PowerpointExtractor extends Extractor {
    /**
     * Extract content from a powerpoint pptx file.
     * @param $filepath
     * @return string
     */
    protected static function extractContentFromPptx($filepath): string
    {
        $zip_handle = new \ZipArchive();
        $response   = '';

        if (true === $zip_handle->open($filepath)) {

            $slide_number = 1; //loop through slide files
            $doc = new \DOMDocument();

            while (($xml_index = $zip_handle->locateName('ppt/slides/slide' . $slide_number . '.xml')) !== false) {

                $xml_data   = $zip_handle->getFromIndex($xml_index);

                $doc->loadXML($xml_data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $response  .= strip_tags($doc->saveXML());

                $slide_number++;

            }
            $zip_handle->close();
        }
        return $response;
    }

    public static function extractText($filepath): string
    {
        return static::extractContentFromPptx($filepath);
    }

    public static function canHandleFile($filepath): bool
    {
        return ZipHelper::isZipFile($filepath);
    }
}