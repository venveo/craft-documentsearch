<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\services;

use Craft;
use craft\base\Component;
use craft\base\Volume;
use craft\elements\Asset;
use craft\helpers\Db;
use phpDocumentor\Reflection\Types\String_;
use venveo\documentsearch\DocumentSearch as Plugin;
use venveo\documentsearch\models\Settings;
use voku\helper\StopWordsLanguageNotExists;
use yii\db\Schema;

/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class DocumentContentService extends Component
{
    /**
     * @param Asset $asset
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getAssetContentKeywords(Asset $asset): ?string
    {
        /** @var Settings $settings */
        $settings = Plugin::$plugin->getSettings();

        // check to make sure the volume is allowed to be indexed
        /** @var Volume $volume */
        $volume = $asset->getVolume();
        if (!in_array($volume->id, $settings->indexVolumes, true)) {
            return null;
        }

        // make sure the asset size doesn't exceed our maximum
        if ($asset->size > $settings->maximumDocumentSize * 1024) {
            Craft::info('Skipping asset ('.$asset->id.') because it exceeds maximumDocumentSize ('.$settings->maximumDocumentSize.')', __METHOD__);
            return null;
        }

        /*
         * Add support for common document types. Update pdf support to use a native php solution.
         */
        switch($asset->kind){
            case Asset::KIND_PDF:
                $filepath = $asset->getCopyOfFile();
                Craft::info('Asset(pdf) path is: '. $filepath,__METHOD__);
                $text = $this->extractContentFromPDF($filepath);
                break;
            case Asset::KIND_EXCEL:
                $filepath = $asset->getCopyOfFile();
                Craft::info('Asset(excel) path is: '. $filepath,__METHOD__);
                $text = $this->extractContentFromExcel($filepath);
                break;
            case Asset::KIND_WORD:
                $filepath = $asset->getCopyOfFile();
                Craft::info('Asset(word) path is: '. $filepath,__METHOD__);
                $text = $this->extractContentFromWord($filepath);
                break;
            case Asset::KIND_POWERPOINT:
                $filepath = $asset->getCopyOfFile();
                Craft::info('Asset(presentation) path is: '. $filepath,__METHOD__);
                $text = $this->extractContentFromPresentation($filepath);
                break;
            case Asset::KIND_TEXT:
                $filepath = $asset->getCopyOfFile();
                Craft::info('Asset(text) path is: '. $filepath,__METHOD__);
                $text = file_get_contents($filepath,false);
                break;
            default:
                //no op;
                Craft::warning('Document search cannot index ' . $asset->kind . '. Path is:'. $filepath,__METHOD__);
        }

        // If we have text, let's extract the keywords from it
        if (isset($text)) {
            // Try to figure out what language the document is in
            $language = $asset->getSite()->language ?: 'en';
            $languageParts = explode('-', $language);
            $languageShort = strtolower(array_shift($languageParts));

            // If we can - let's just store the entire text
            $db = Craft::$app->getDb();
            if ($isPgsql = $db->getIsPgsql()) {
                $maxSize = Craft::$app->search->maxPostgresKeywordLength;
            } else {
                $maxSize = Db::getTextualColumnStorageCapacity(Schema::TYPE_TEXT);
            }
            if (mb_strlen($text) < $maxSize) {
                return $text;
            }

            $scoredKeywords_1 = Plugin::$plugin->rake->get($text, 1, $languageShort);
            $scoredKeywords_2 = Plugin::$plugin->rake->get($text, 2, $languageShort);
            $scoredKeywords_3 = Plugin::$plugin->rake->get($text, 3, $languageShort);
            $count = count($scoredKeywords_1) + count($scoredKeywords_2) + count($scoredKeywords_3);

            // If there are more than 100 keywords, let's just get the first third
            if ($count > 100) {
                $scoredKeywords = array_slice($scoredKeywords_1, 0, 30) +
                    array_slice($scoredKeywords_2, 0, 30) +
                    array_slice($scoredKeywords_3, 0, 30);
            } else {
                $scoredKeywords = $scoredKeywords_1 + $scoredKeywords_2 + $scoredKeywords_3;
            }

            // Assemble the keywords into a string
            $results = implode(' ', array_keys($scoredKeywords));
            Craft::info('Extracted '.count($scoredKeywords).' keywords from: '.$asset->id.' in '.$languageShort, __METHOD__);
        } else {
            Craft::info('No text found in '.$asset->id, __METHOD__);
            return null;
        }
        return $results;
    }

    /**
     * Extract textual content from Asset::KIND_PDF
     * 
     * @param string $filepath
     * @return string
     */
    public function extractContentFromPDF($filepath): string
    {
        Craft::info('Extracting text content from PDF : '.$filepath, __METHOD__);
        $parser   = new \Smalot\PdfParser\Parser();
        $pdf      = $parser->parseFile( $filepath );
        return $pdf->getText();
    }

    /**
     * Detect if a file is a zip file.
     * @param $filepath
     * @return bool
     */
    public function isZipFile($filepath){
        $fh = fopen($filepath,'r');
        $bytes = fread($fh,4);
        fclose($fh);
        return ('504b0304' === bin2hex($bytes));
    }

    /**
     * Quick method for extracting text from a word 2007+ document.
     * @param $filepath
     * @return bool|string
     * //TOOD Conver to ZipArchive object.
     */
    public function extractContentFromDocx($filepath ): string
    {
        $response = '';

        $zip = zip_open($filepath);

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE)
                continue;

            if (zip_entry_name($zip_entry) != 'word/document.xml')
                continue;

            $response .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }

        zip_close($zip);

        $response = str_replace('</w:r></w:p></w:tc><w:tc>', ' ', $response);
        $response = str_replace('</w:r></w:p>', "\r\n", $response);
        $response = strip_tags($response);

        if(empty($response)){
            $response = '';
        }

        return $response;
    }

    /**
     * Quick method for extracting text from a word 97 document.
     * @param $filepath
     * @return string
     */
    protected function extractContentFromDoc($filepath): string
    {
        $fileHandle = fopen($filepath, 'r');
        $line       = @fread($fileHandle, filesize($filepath));
        $lines      = explode(chr(0x0D), $line);
        $response   = '';

        foreach ($lines as $current_line) {

            $pos = strpos($current_line, chr(0x00));

            if ( ($pos !== FALSE) || (strlen($current_line) == 0) ) {
                //no op
            } else {
                $response .= $current_line . ' ';
            }
        }

        $response = preg_replace('/[^a-zA-Z0-9\s,.\-\n\r\t@\/_()]/', '', $response);

        $nl = stripos($response,"\n");
        if($nl){
            $response = substr($response,0,$nl);
        }
        return $response;
    }

    /**
     * Extract text for any type included in Asset::KIND_WORD.
     * @see craft/vendor/craftcms/cms/src/helpers/Assets.php Line 442
     * 
     * @param $filepath
     * @return string
     */
    public function extractContentFromWord($filepath): string
    {
        Craft::info('Extracting text content from Word doc : '.$filepath, __METHOD__);

        if($this->isZipFile($filepath)){
            $text = $this->extractContentFromDocx($filepath);
        }else{
            $text = $this->extractContentFromDoc($filepath);
        }
        return $text;
    }

    /**
     * Extract text from a Excel 2007 file (.xlsx)
     * @param $filepath
     * @return string
     */
    public function extractContentFromXlsx( $filepath ): string
    {
        $xml_filename = 'xl/sharedStrings.xml'; //content file name
        $zip_handle   = new \ZipArchive();
        $response     = '';

        if (true === $zip_handle->open($filepath)) {

            if (($xml_index = $zip_handle->locateName($xml_filename)) !== false) {

                $doc = new \DOMDocument();

                $xml_data   = $zip_handle->getFromIndex($xml_index);
                $doc->loadXML($xml_data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $response   = strip_tags($doc->saveXML());
            }
            $zip_handle->close();
        }
        return $response;
    }

    /**
     * Extract text for any type included in Asset::KIND_EXCEL.
     * @see craft/vendor/craftcms/cms/src/helpers/Assets.php Line 442
     * 
     * @param $filepath
     * @return string
     */
    public function extractContentFromExcel($filepath): string
    {
        Craft::info('Extracting text content from Excel doc : '.$filepath, __METHOD__);

        if($this->isZipFile($filepath)){
            $text = $this->extractContentFromXlsx($filepath);
        }else{
            //TODO: Add support for excel 97 (.xls) documents.
            Craft::info('Cannot extract text from ' . $filepath,__METHOD__);
            $text = '';
        }
        return $text;
    }

    /**
     * Extract content from a powerpoint pptx file.
     * @param $filepath
     * @return string
     */
    public function extractContentFromPptx($filepath): string
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

    /**
     * Extract text for any type included in Asset::KIND_POWERPOINT.
     * @see craft/vendor/craftcms/cms/src/helpers/Assets.php Line 525
     * 
     * @param $filepath
     * @return string
     */
    public function extractContentFromPresentation($filepath): string
    {
        Craft::info('Extracting text content from Presentation doc : '.$filepath, __METHOD__);

        if($this->isZipFile($filepath)){
            $text = $this->extractContentFromPptx($filepath);
        }else{
            //TODO: Add support for powerpoint 97 (.ppt) documents
            Craft::info('Cannot extract text from ' . $filepath,__METHOD__);
            $text = '';
        }
        return $text;
    }
}