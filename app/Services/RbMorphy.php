<?php
namespace App\Services;
$file = '/phpmorphy/src/common.php';

require_once($file);

/**
 * Оболочка для библиотеки phpMorphy
 *
 * @author Ilya Perfilyev <ilya_perfi@mail.ru>
 */
class RBMorphy {

    public $options = array(
        // storage type, follow types supported
        // PHPMORPHY_STORAGE_FILE - use file operations(fread, fseek) for dictionary access, this is very slow...
        // PHPMORPHY_STORAGE_SHM - load dictionary in shared memory(using shmop php extension), this is preferred mode
        // PHPMORPHY_STORAGE_MEM - load dict to memory each time when phpMorphy intialized, this useful when shmop ext. not activated. Speed same as for PHPMORPHY_STORAGE_SHM type
        'storage' => PHPMORPHY_STORAGE_FILE,
        // Extend graminfo for getAllFormsWithGramInfo method call
        'with_gramtab' => false,
        // Enable prediction by suffix
        'predict_by_suffix' => true,
        // Enable prediction by prefix
        'predict_by_db' => true
    );
    public $dictdir;
    public $language = 'rus';
    protected $_phpMorphy;

    protected function getDictDir() {
        return isset($this->dictdir) ? $this->dictdir : 'application.lib.phpmorphy.dicts';
    }

    public function getMorphy() {
        if (!isset($this->_phpMorphy)) {
            // Create descriptor for dictionary located in $dir directory with russian language
            $dict_bundle = new phpMorphy_FilesBundle($this->getDictDir(), $this->language);
            // Create phpMorphy instance
            $this->_phpMorphy = new phpMorphy($dict_bundle, $this->options);
        }
        return $this->_phpMorphy;
    }

    public function toUpperText($text) {
        $upperText = mb_strtoupper($text);
        return str_replace('Ё', 'Е', $upperText);
    }

    /**
     * Возвращает количество уникальных слов из фразы $query, которые присутствуют в тексте $text
     * (учитывая различные словофрмы)
     * @param string $query
     * @param string $text
     * @return int
     */
    public function getWordsIntersection($query, $text) {
        // 1. Приводим фразу и текст к верхнемк регистру
        // слова в словаре записаны в верхнем регистре + так обеспечится регистронезависимый поиск
        $upperQuery = $this->toUpperText($query);

        $upperText = $this->toUpperText($text);

        // 2. распиливаем фразу и текст на слова
        $keywords = Util::getWords($upperQuery);
        $textWords = Util::getWords($upperText);
        //echo "textwords: ".implode(',',$textWords)."\n";

        // 3. Получаем словоформы слов фразы
        $keywordsMap = array();
        if(!empty($keywords)) foreach ($keywords as $w) {
            $all_forms = $this->getMorphy()->getAllForms($w);
            if (is_array($all_forms)) {
                foreach ($all_forms as $fw) {
                    $keywordsMap[$w][] = $fw;
                }
                //echo "wforms:{$w} >>> ".implode(',',$keywordsMap[$w])."\n";
            } else {
                $keywordsMap[$w][] = $w;
            }
        }

        $foundCounter = 0;
        //ищем словоформы в тексте
        foreach ($keywordsMap as $w => $wForms) {
            foreach ($wForms as $wForm) {
                if (in_array($wForm, $textWords)) {
                    $foundCounter++;
                    break;
                }
            }
        }
        return $foundCounter;
    }

    /**
     * Возвращает процент количество уникальных слов из фразы $query, которые присутствуют в тексте $text
     * (учитывая различные словофрмы)
     * @param string $query
     * @param string $text
     * @return int
     */
    public function getPercentWordsIntersection($query, $text) {
        // 1. Приводим фразу и текст к верхнемк регистру
        // слова в словаре записаны в верхнем регистре + так обеспечится регистронезависимый поиск
        $upperQuery = $this->toUpperText($query);

        $upperText = $this->toUpperText($text);

        // 2. распиливаем фразу и текст на слова
        $keywords = WordsForms::getWords($upperQuery);
        $cntKeywords = count($keywords);
        $textWords = WordsForms::getWords($upperText);
        // 3. Получаем словоформы слов фразы
        $keywordsMap = array();
        if(!empty($keywords)) foreach ($keywords as $w) {
            $all_forms = $this->getMorphy()->getAllForms($w);
            if (is_array($all_forms)) {
                foreach ($all_forms as $fw) {
                    $keywordsMap[$w][] = $fw;
                }
                //echo "wforms:{$w} >>> ".implode(',',$keywordsMap[$w])."\n";
            } else {
                $keywordsMap[$w][] = $w;
            }
        }

        $foundCounter = 0;
        //ищем словоформы в тексте
        foreach ($keywordsMap as $w => $wForms) {
            foreach ($wForms as $wForm) {
                if (in_array($wForm, $textWords)) {
                    $foundCounter++;
                    break;
                }
            }
        }
        return $foundCounter/$cntKeywords;
    }

    public function addBaseForms($word, &$data) {
        $bfs = $this->getBaseForms($word);
        foreach ($bfs as $bf) {
            $data[$bf] = $word;
        }
    }

    public function getBaseForms($word) {
        $w = $this->toUpperText($word);
        if(empty($w)) {
            return array();
        }
        $f = $this->getMorphy()->getBaseForm($w);
        $result = empty($f) ? array($w) : $f;
        //echo "getBaseForms: {$word} -> ".implode(',',$result)."\n";
        return $result;
    }

    public function getBaseWords($text) {
        //echo "\ngetBaseWords: {$text} -> ";
        $baseWords = array();
        $words = explode(' ', $text);
        foreach ($words as $w) {
            $this->addBaseForms($w, $baseWords);
        }
        $result = array_keys($baseWords);
        //echo implode(',', $result);
        return $result;
    }

    public function getfindWord($word) {
        $w = $this->toUpperText($word);
        if(empty($w)) {
            return array();
        }
        $f = $this->getMorphy()->getAllForms($w);
        $result = empty($f) ? array($w) : $f;
        //echo "getBaseForms: {$word} -> ".implode(',',$result)."\n";
        return $result;
    }

}

