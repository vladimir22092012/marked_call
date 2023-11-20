<?php
namespace App\Services;

use App\Services\RbMorphy;

class WordsForms
{
    protected $lang = 'ru';

    /**
     * @var RbMorphy
     */
    protected $phpMorphyClass;

    protected $phpMorphy;

    const LANGUAGES = [
        'ru' => 'ru_RU',
        'en' => 'en_EN',
        'de' => 'de_DE',
    ];

    const EXCEPTION_WORD = [
        'лет'
    ];


    public function __construct($lang) {
        $this->lang = $lang;
    }

    public function getLang() {
        return $this->getPhpMorphyLanguage();
    }

    public function get($data) {
        /** @var phpMorphy $morphy */
        $morphy = self::getPhpMorphy();
        $morphyClass = self::getPhpMorphyClass();

        $data = array_map(function($item) use ($morphyClass) {
            return $morphyClass->toUpperText($item);
        }, $data);

        $wordsForms = $morphy->getAllForms($data);

        $return = [];
        foreach ($wordsForms as $key => $item) {
            $key = mb_strtolower($key);
            if($item === false) {
                $item = [$key];
            }
            foreach ($item as &$subItem) {
                $subItem = mb_strtolower($subItem);
            }

            $return[$key] = $item;
        }

        return $return;
    }

    public function getLemmas($data, $full = false) {
        $morphy = $this->getPhpMorphy();

        $morphyClass = $this->getPhpMorphyClass();

        $data = array_map(function($item) use ($morphyClass) {
            return $morphyClass->toUpperText($item);
        }, $data);

        $lemmas = $morphy->lemmatize($data);
        $result = [];

        foreach ($lemmas as $key => $lemma) {
            $key = mb_strtolower($key);
            if($full === false) {
                $lemma = empty($lemma) ? $lemma : mb_strtolower($lemma[0]);
            } else {
                $lemma = empty($lemma) ? $lemma : array_map(function ($l) {return mb_strtolower($l);}, $lemma);
            }
            $result[$key] = empty($lemma) ? $key : $lemma;
        }

        return $result;
    }

    public function getPhpMorphyClass() {
        if(is_null($this->phpMorphyClass)) {
            $morphyClass = new RbMorphy();
            $this->phpMorphyClass = $morphyClass;
        }
        return $this->phpMorphyClass;
    }

    public function getPhpMorphy() {
        if(is_null($this->phpMorphy)) {
            /** @var $phpMorphy RBMorphy */
            $phpMorphy = $this->getPhpMorphyClass();
            $this->phpMorphy = $phpMorphy->getMorphy();
        }
        return $this->phpMorphy;
    }

    public function getPhpMorphyLanguage() {
        $languages = [
            'ru' => 'ru_RU',
            'en' => 'en_EN',
            'de' => 'de_DE',
        ];

        return $languages[$this->lang] ?? $languages['ru'];
    }

    public static function getPhpMorphyWordsFormsDetails($data, $lang) {
        /** @var phpMorphy $morphy */
        $self = new self($lang);
        $morphy = $self->getPhpMorphy();
        $morphyClass = $self->getPhpMorphyClass();

        $data = array_map(function($item) use ($morphyClass) {
            return $morphyClass->toUpperText($item);
        }, $data);

        $fullInfo = $morphy->getAllFormsWithGramInfo($data, true);

        $return = [];
        foreach ($fullInfo as $key => $parentItem) {
            $key = mb_strtolower($key);
            if($parentItem === false) {
                $parentItem = [];
            }

            $returnItem = [];
            foreach ($parentItem as $item) {
                foreach ($item['forms'] as &$subItem) {
                    $subItem = mb_strtolower($subItem);
                }

                $returnItem[] = $item;
            }

            $return[$key] = $returnItem;
        }

        return $return;
    }

    public static function getPhpMorphyWordsForms($data, $lang) {
        /** @var phpMorphy $morphy */
        $self = new self($lang);
        $morphy = $self->getPhpMorphy();
        $morphyClass = $self->getPhpMorphyClass();

        foreach ($data as $phrase) {
            $words = explode(' ', $phrase);

            $array = [];

            if(count($words) > 2) {
                $word = implode(' ', $words);

                $forms = [];
                if (!empty($word)) {
                    $wordsForms = $morphy->getAllForms($morphyClass->toUpperText($word));

                    $forms = [];
                    if ($wordsForms !== false) {
                        foreach ($wordsForms as $key => $item) {
                            $key = mb_strtolower($key);
                            if ($item === false) {
                                $item = [$key];
                            }

                            $forms[$key] = mb_strtolower($item);
                        }
                    } else {
                        $forms = [$word];
                    }

                    $array[] = $forms;
                }

                $return[$phrase] = array_values($forms);

            } else {
                foreach ($words as $word) {
                    if (!empty($word)) {
                        $wordsForms = $morphy->getAllForms($morphyClass->toUpperText($word));

                        $forms = [];
                        if ($wordsForms !== false) {
                            foreach ($wordsForms as $key => $item) {
                                $key = mb_strtolower($key);
                                if ($item === false) {
                                    $item = [$key];
                                }

                                $forms[$key] = mb_strtolower($item);
                            }
                        } else {
                            $forms = [$word];
                        }

                        $array[] = $forms;
                    }
                }
            }

            $forms = [];

            if ($array[0]) {
                foreach ($array[0] as $phr) {
                    if (empty($phr)) {
                        continue;
                    }
                    $phr = trim($phr);
                    $forms[$phr] = $phr;
                }

                unset($array[0]);

                foreach ($array as $phrArray) {
                    foreach ($forms as $form => $value) {
                        foreach ($phrArray as $phr) {
                            $newPhr = $forms[$form] . ' ' . trim($phr);
                            $forms[$newPhr] = $newPhr;
                        }
                        unset($forms[$form]);
                    }
                }
            }

            $return[$phrase] = array_values($forms);
        }


        return $return;
    }

    public static function getWordsLemma($words, $lang) {
        $self = new self($lang);
        $morphy = $self->getPhpMorphy();
        $morphyClass = $self->getPhpMorphyClass();

        $upperWords = array_map(function($item) use ($morphyClass) {
            return $morphyClass->toUpperText($item);
        }, $words);

//        $lemms = $morphy->lemmatize($upperWords, phpMorphy::IGNORE_PREDICT);
        $lemms = $morphy->lemmatize($upperWords);

        $return = [];
        if(!empty($lemms)) {
            foreach ($lemms as $key => $item) {
                $key = mb_strtolower($key);
                if($item === false) {
                    $item = [$key];
                }

//                $item = array_filter($item, function($word) { return !empty($word);});
                $return[$key] = array_map(function ($word) {
                    return  mb_strtolower($word);
                }, $item);
            }
        }

        return $return;
    }

    /**
     * Возвращает массив слов, входящих в текст
     * где слово - это подряд идущие символы (максимально возможное количество), удовлетворяющие регулярному выражению
     * @param string $text
     * @param string $reg регулярное выражение, определяющее символы слов, по умолчанию все возмжные символы слов. Это буквы, цифры и знак _
     * при этом знак подчеркивания потом добавляем в разделитель
     * @return string[]
     */
    public static function getWords($text, $reg = '/\w/ui') {
        $token = preg_replace($reg, '', $text);
        $token .= "_";
        $tok = strtok($text, $token);
        $res = array();
        while ($tok !== false) {
            $res[] = $tok;
            $tok = strtok($token);
        }
        return $res;
    }
}
