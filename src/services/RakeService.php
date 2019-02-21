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

use craft\base\Component;
use TextAnalysis\Analysis\Keywords\Rake;
use TextAnalysis\Documents\TokensDocument;
use TextAnalysis\Filters;
use TextAnalysis\Tokenizers\WhitespaceTokenizer;
use voku\helper\StopWords;
use voku\helper\StopWordsLanguageNotExists;


/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class RakeService extends Component
{
    const NGRAM_SIZE = 3;

    /**
     * @var \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    protected $tokenFilters = [];

    /**
     * @var \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    protected $contentFilters = [];


    public function get($content)
    {
        foreach ($this->getContentFilters() as $contentFilter) {
            $content = $contentFilter->transform($content);
        }
        return $this->getKeywordScores($content);
    }

    /**
     *
     * @return \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    public function getContentFilters()
    {
        if (empty($this->contentFilters)) {

            $lambdaFunc = function($word) {
                return preg_replace('/[\x00-\x1F\x80-\xFF]/u', ' ', $word);
            };

            $this->contentFilters = [
                new Filters\StripTagsFilter(),
                new Filters\LowerCaseFilter(),
                new Filters\NumbersFilter(),
                new Filters\EmailFilter(),
                new Filters\UrlFilter(),
                new Filters\PossessiveNounFilter(),
                new Filters\QuotesFilter(),
                new Filters\PunctuationFilter(),
                new Filters\CharFilter(),
                new Filters\LambdaFilter($lambdaFunc),
                new Filters\WhitespaceFilter()
            ];
        }
        return $this->contentFilters;
    }

    /**
     *
     * @return \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    public function getTokenFilters($language): array
    {
        $stopWords = new StopWords();
        try {
            $localizedStopWords = $stopWords->getStopWordsFromLanguage($language);
        } catch (StopWordsLanguageNotExists $e) {
            $localizedStopWords = $stopWords->getStopWordsFromLanguage('en');
        }
        if (empty($this->tokenFilters)) {
            $this->tokenFilters = [
                new Filters\StopWordsFilter($localizedStopWords),
            ];
        }
        return $this->tokenFilters;
    }

    /**
     *
     * @param string $content
     * @param string $language The language to use to lookup stop words
     * @return array
     */
    public function getKeywordScores($content, $language = 'en'): array
    {
        $tokens = (new WhitespaceTokenizer())->tokenize($content);
        $tokenDoc = new TokensDocument(array_map('strval', $tokens));
        unset($tokens);

        foreach ($this->getTokenFilters($language) as $filter) {
            $tokenDoc->applyTransformation($filter, false);
        }

        $size = count($tokenDoc->toArray());
        if ($size < self::NGRAM_SIZE) {
            return [];
        }

        $rake = new Rake($tokenDoc, self::NGRAM_SIZE);
        return $rake->getKeywordScores();
    }
}
