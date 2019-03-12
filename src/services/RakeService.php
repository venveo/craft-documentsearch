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
use TextAnalysis\Analysis\FreqDist;
use TextAnalysis\Analysis\Keywords\Rake;
use TextAnalysis\Documents\TokensDocument;
use TextAnalysis\Filters;
use TextAnalysis\Interfaces\ITokenTransformation;
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
     * @var ITokenTransformation[]
     */
    protected $tokenFilters = [];

    /**
     * @var ITokenTransformation[]
     */
    protected $contentFilters = [];


    public function get($content, $ngramSize = self::NGRAM_SIZE, $language = 'en'): array
    {
        foreach ($this->getContentFilters() as $contentFilter) {
            $content = $contentFilter->transform($content);
        }
        return $this->getKeywordScores($content, $ngramSize, $language);
    }

    /**
     *
     * @return ITokenTransformation[]
     */
    public function getContentFilters(): array
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
     * @param $language
     * @return ITokenTransformation[]
     * @throws StopWordsLanguageNotExists
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
     * @param int $ngramSize
     * @param string $language The language to use to lookup stop words
     * @return array
     * @throws StopWordsLanguageNotExists
     */
    public function getKeywordScores($content, $ngramSize = self::NGRAM_SIZE, $language = 'en'): array
    {
        $tokens = (new WhitespaceTokenizer())->tokenize($content);
        $tokenDoc = new TokensDocument(array_map('strval', $tokens));
        unset($tokens);

        foreach ($this->getTokenFilters($language) as $filter) {
            $tokenDoc->applyTransformation($filter, false);
        }

        $size = count($tokenDoc->toArray());
        if ($size < $ngramSize) {
            return [];
        }

        $rake = new Rake($tokenDoc, $ngramSize);
        if ($ngramSize === 1) {
            return (new FreqDist($rake->getTokens()))->getKeyValuesByFrequency();
        }
        return $rake->getKeywordScores();
    }
}
