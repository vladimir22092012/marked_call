<?php

namespace App\Jobs;

use App\Models\MarkedCallLog;
use App\Models\ProjectWordstat;
use App\Services\WordsForms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarkedCall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $event;
    private $owner_id;

    const LIMIT_INDEX_WORD_IN_PHRASE = 4;
    const DEFAULT_SCHEME_ID = 25;
    private $links_words = null;
    private $lemmas = null;
    private $wordsBasis = null;
    private $tags_call = null;
    private $project_id = null;
    private ?ProjectWordstat $_project;
    private $_words_basis = null;
    private $text_call = null;
    private $_map = null;
    public $text_channel = [];

    public $user;

    public function __construct($event, $project_id, $owner_id, $user)
    {
        $this->event = $event;
        $this->project_id = $project_id;
        $this->owner_id = $owner_id;
        $this->user = $user;
    }

    public function handle(): void
    {
        try {
            $tags = $this->markCall();
            $this->saveThemes($tags);
            MarkedCallLog::create([
                'call_id' => $this->event->id,
                'tags' => serialize($tags),
                'status' => 1,
                'user_id' => $this->user->id,
            ]);
        } catch (\Exception $exception) {
            MarkedCallLog::create([
                'call_id' => $this->event->id,
                'tags' => $exception->getMessage(),
                'status' => 0,
                'user_id' => $this->user->id,
            ]);
        }
    }

    public function markCall()
    {
        if (!empty($this->event->title)) {
            $this->setTextCall($this->event->title);
        } else {
            return false;
        }

        // Получаем слова базисов
        $this->getWordsBasis();

        // Получаем недостающие леммы
        $this->updateLemmasLazy();

        // Индексируем текст
        $this->analysisText();

        // Находим теги с помощью индексов
        $this->analysisTagsText();

        // Решаем конфликты тегов
        $this->conflictTags();

        return $this->getTags();
    }

    public function conflictTags()
    {
        $markers = $this->getTags();

        foreach ($markers as $marker_original => $item) {
            $messages_original = $item['message_number'];

            foreach ($messages_original as $m_original_key => $m_original) {
                $start_original = $m_original['position']['start'];
                $end_original = $m_original['position']['end'];

                foreach ($markers as $marker => $item) {
                    $messages = $item['message_number'];
                    foreach ($messages as $m_key => $m) {
                        if ($m['position']['start'] === $start_original && $m['position']['end'] === $end_original && $marker_original !== $marker) {
                            $next_original = $markers[$marker_original]['message_number'][$m_original_key + 1];
                            $next = $messages[$m_key + 1];
                            if (!empty($next_original) && !empty($next)) {
                                if ($next_original['position']['start'] > $next['position']['start']) {
                                    unset($messages_original[$m_original_key + 1]); #
                                    if (count($messages_original) <= 1) {
                                        $conflictTags[] = $markers[$marker_original]['name'];
                                        $markers[$marker_original]['message_number'] = [];
                                        $markers[$marker_original]['color'] = '#ccc';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function setTextCall($text)
    {
        $array = explode(PHP_EOL, $text);
        foreach ($array as $key => $item) {
            if (!empty($item)) {
                if(strpos($item, "Менеджер") !== false){
                    $this->text_channel[$key] =  "M";
                }
                if(strpos($item, "Клиент") !== false){
                    $this->text_channel[$key] = "C";
                }
            }
        }
//var_dump($this->text_channel[0],$this->text_channel[1]);
        $text = preg_replace('/^(Менеджер|Клиент|Сервис):?\s?(\(.*\))?:?\s?/m', '', $text);
        $array = explode(PHP_EOL, $text);
        $array = array_map(function ($text) {
            $arrayText = explode('-', $text);
            unset($arrayText[0]);
            return trim(implode($arrayText));
        }, $array);
        $this->text_call = implode(PHP_EOL, $array);
    }


    /**
     * Получаем базисы указанного проекта
     */
    private function getWordsBasis()
    {
        if (is_null($this->_words_basis)) {
            /** @var ProjectWordstat $project */
            $project = $this->getProject();
            $this->_words_basis = $project->getWordsFromColumns(true, true);
        }

        return $this->_words_basis;
    }

    public function getProject()
    {
        if (is_null($this->_project)) {
            $this->_project = ProjectWordstat::getProject($this->project_id);
        }

        return $this->_project;
    }


    private function getUniqueWordsTextAndBasis()
    {
        $words = [];
        $textCall = $this->getTextCall();
        $wordsBasis = $this->getWordsBasis();

        foreach ($wordsBasis as $marker => $wordsBasisArray) {
            foreach ($wordsBasisArray as $word) {
                if (empty(trim($word))) continue;
                $wordExplode = explode(' ', self::clearIndex($word));
                foreach ($wordExplode as $w) {
                    $words[$w] = true;
                }
            }
        }

        $textCall = mb_strtolower($textCall);
        $textCall = preg_replace("/[\n|,|.|!|@|#|$|%|^|&|*|(|)|_|+|=|-|>|<|;|:|©|\"|'|–]/u", " ", $textCall);
        $textCall = preg_replace("/\s{2,}/u", " ", $textCall);
        $wordsCall = explode(' ', $textCall);
        foreach ($wordsCall as $word) {
            if (empty(trim($word))) continue;
            $wordExplode = explode(' ', self::clearIndex($word));
            foreach ($wordExplode as $w) {
                $words[$w] = true;
            }
        }

        return $words;
    }

    /**
     * Получаем леммы
     */
    private function updateLemmasLazy()
    {
        $lemmas = $this->getLemmas();
        $words = $this->getUniqueWordsTextAndBasis();
        $keys = array_keys($words);
        foreach ($keys as $key => $word) {
            if (isset($lemmas[$word])) {
                unset($keys[$key]);
            }
        }

        $getLemmas = (new WordsForms('ru'))->getLemmas($keys, true);

        foreach ($getLemmas as $original => $lemma) {
            if (!is_array($lemma)) {
                $lemma = [$lemma];
            }
            $this->lemmas[$original] = $lemma;
        }
    }

    private function getLemmas()
    {
        return $this->lemmas;
    }

    public static function clearIndex($text)
    {
        $wrongWord = ['в', 'без', 'до', 'из', 'к', 'на', 'по', 'о', 'от', 'перед', 'при', 'через', 'с', 'у', 'за', 'над', 'об', 'под', 'про', 'для', 'и', 'да', 'только', 'но', 'ни', 'как', 'так', 'сколько', 'столько', 'или', 'либо', 'то', 'ли', 'зато', 'же', 'все', 'чтобы', 'есть', 'а', 'что', 'когда', 'лишь', 'едва', 'где', 'куда', 'откуда', 'настолько', 'такой', 'степени', 'дабы'];
        $text = preg_replace("/[\n|\t|,|\.|!|@|#|$|%|^|&|*|(|)|_|+|-|=|-|-|_|\/|\\|:|;]/u", ' ', $text);
        $text = preg_replace("/\\s(" . join("|", $wrongWord) . ")\\s/u", ' ', $text);
        $text = preg_replace("/ {2,}/u", " ", $text);
        return trim($text);
    }

    public static function joinArrayPhraseIndex($array)
    {
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

        return array_values($forms);
    }

    private function WordsPush($i, $text, &$startWord, &$words)
    {
        $word = mb_substr($text, $startWord, $i - $startWord, "UTF-8");
//            var_dump(['$word' => $word]);

        $words[] = [
            'text' => $word,
            'start' => $startWord,
            'end' => $i,
        ];

        $startWord = false;
    }

    /**
     * Производим индексацию текста
     * @return null
     */
    private function analysisText()
    {
        $index = [];

        $text = $this->getTextCall();

        $array = explode(PHP_EOL, $text);
        $len = 0;
        $map = [];
        foreach ($array as $key => $item) {
            if (!empty($item)) {
                $channel = $this->text_channel[$key];
                $map[$key] = [
                    'id' => $key,
                    'position' => $len,
                    'text' => $item,
                    "channel"=>$channel
                ];
                $len += mb_strlen($item) + 1;
            }
        }
        $this->setMap($map);

        $lemmas = $this->getLemmas();

        $text = mb_strtolower($text);
        $words = [];
        $startWord = false;

        $length = mb_strlen($text, "UTF-8");
        for ($i = 0; $i <= $length; $i++) {
            $char = mb_substr($text, $i, 1, "UTF-8");

            if (array_search($char, [' ', ' ', '\n', '\t', "\r\n", "\r", ',', '!', '?', PHP_EOL, '(', ')', '–', '-', ':', ';', '«', '»', '\\', '/']) !== false) {
                if ($startWord !== false) {
                    self::WordsPush($i, $text, $startWord, $words);
                }
                $startWord = false;
                continue;
            } else if ($startWord === false) {
                $startWord = $i;
            } else {
                if (mb_substr($text, $i + 1, 1, "UTF-8") === ' ' || $i + 1 > $length) {
                    self::WordsPush($i + 1, $text, $startWord, $words);
                }
            }
        }

        $wordsTemp = $words;
        $wordsLength = count($wordsTemp);

        for ($x = 0; $x <= $wordsLength; $x++) {
            for ($y = 2; $y <= self::LIMIT_INDEX_WORD_IN_PHRASE; $y++) {
                if (!isset($wordsTemp[$x + $y - 1])) continue;
                $newArray = array_slice($wordsTemp, $x, $y);
                if (count($newArray) > 1) {
                    $word = array_map(function ($item) {
                        return $item['text'];
                    }, $newArray);
                    $word = implode(' ', $word);
                    $coordinate = [
                        "text" => $word,
                        "start" => $newArray[0]['start'],
                        "end" => $newArray[count($newArray) - 1]['end']
                    ];

                    $words[] = $coordinate;
                }
            }
        }

        usort($words, function ($a, $b) {
            return $a['start'] - $b['start'];
        });

        $indexWords = [];
        foreach ($words as $item) {
            $index = trim($item['text']);

            $indexWordsArray = explode(' ', $index);

            $index = array_map(function ($word) use ($lemmas) {
                $word = self::clearIndex($word);
                return $word ? ($lemmas[$word] ?? [$word]) : [$word];
            }, $indexWordsArray);


            $indexes = self::joinArrayPhraseIndex($index);

            foreach ($indexes as $index) {
                $index = self::clearIndex($index);
                if (!isset($indexWords[$index])) {
                    $indexWords[$index] = [];
                }

                $indexWords[$index][] = [
                    "start" => $item['start'],
                    "end" => $item['end'],
                ];

            }
        }

        $this->setLinksWords($indexWords);

        return $this->getLinksWords();
    }

    private function getTextCall()
    {
        return $this->text_call;
    }

    private function getLinksWords()
    {
        return $this->links_words;
    }

    private function setLinksWords($array)
    {
        $this->links_words = $array;
    }

    private function analysisTagsText()
    {
        $index = $this->getLinksWords();
        $wordsBasis = $this->getWordsBasis();
        $lemmas = $this->getLemmas();
        $textCall = $this->getTextCall();
        $map = $this->getMap();

        foreach ($wordsBasis as $marker => $words) {
            $marker = explode('_', $marker);
            $color = array_pop($marker);
            $marker = implode('_', $marker);
            $marker = str_replace("?","",$marker);
            $marker = str_replace("!","",$marker);
            $marker = str_replace('"',"",$marker);
            $this->addTag($marker);
        }

        foreach ($wordsBasis as $marker => $words) {

            $marker = explode('_', $marker);
            $color = array_pop($marker);
            $marker = implode('_', $marker);
            $first_char = mb_substr($marker, 0, 1);
            $foundmasrker = false;
            $needchnnel = "all";
            $onlyOneTag = false;
            if($first_char == "!"){
                $needchnnel = "M";
            }
            if($first_char == "?"){
                $needchnnel = "C";
            }
            if($first_char == "!" ||  $first_char == "?"){
                $marker = str_replace("?","",$marker);
                $marker = str_replace("!","",$marker);
            }
            $first_char = mb_substr($marker, 0, 1);
            $last_char = mb_substr($marker, -1);;
            if($first_char === '"' && $last_char === '"') {
                $onlyOneTag = true;
                $marker = str_replace('"',"", $marker);
            }

            $wordsResult = [];
            $allCoordinates = [];

            foreach ($words as $key => $word) {

                $temp = array_map(function ($word) use ($lemmas) {
                    $word = self::clearIndex($word);
                    return $lemmas[$word] ?? [$word];
                }, explode(' ', $word));

                $indexes = self::joinArrayPhraseIndex($temp);
                foreach ($indexes as $word) {
                    $wordsResult[] = $word;
                }
            }

            foreach ($wordsResult as $word) {
                $word = self::clearIndex($word);
                if (isset($index[$word])) {
                    foreach ($index[$word] as $item) {
                        $allCoordinates[] = [
                            "start" => $item['start'],
                            "end" => $item['end'],
                            "word" => $word
                        ];
                    }
                }
            }


            usort($allCoordinates, function ($a, $b) {
                return $a['start'] - $b['start'];
            });

//            foreach ($allCoordinates as $pos => $item) {
//                if (count($allCoordinates) > 1) {
//                    foreach ($allCoordinates as $posFind => $itemFind) {
//                        if ((int)$posFind === (int)$pos) break;
//
//                        if ($item['start'] >= $itemFind['start'] && $item['end'] <= $itemFind['end']) {
//                            unset($allCoordinates[$pos]);
//                            break;
//                        } else if (($item['start'] >= $itemFind['start'] && $item['start'] <= $itemFind['end']) || ($item['end'] >= $itemFind['start'] && $item['end'] <= $itemFind['end'])) {
//                            unset($allCoordinates[$posFind]);
//                        }
//                    }
//                }
//            }

            $allCoordinates = array_map(function ($item) {
                return json_encode($item, JSON_UNESCAPED_UNICODE);
            }, $allCoordinates);
            $allCoordinates = array_unique($allCoordinates);

            $allCoordinates = array_map(function ($item) {
                return json_decode($item, true);
            }, $allCoordinates);

            $allCoordinates = array_values($allCoordinates);

            $allCoordinatesLength = count($allCoordinates);
            $markers = $this->tags_call;
            $mapLength = count($map);
            if($allCoordinatesLength >= 1 && $onlyOneTag) {
                for ($a = 0; $a < $allCoordinatesLength; $a++) {
                    foreach ($map as $key => $item) {
                        $message_number = $item['id'];
                        $position = $item['position'];
                        if ($allCoordinates[$a]['start'] >= $position && isset($map[$key + 1]) ?
                            $allCoordinates[$a]['start'] < $map[$key + 1]['position'] :
                            !isset($map[$key + 1]) && $allCoordinates[$a]['start'] >= $position
                        ) {
                            $this->addMessageTag($marker, [
                                'id' => $message_number,
                                'position' => ['start' => $allCoordinates[$a]['start'], 'end' => $allCoordinates[$a]['end']],
                                "channel" => $this->text_channel[$key],
                                "needchannel" => $needchnnel
                            ], $color);
                            break;
                        }
                    }
                }
            } else {
                for ($a = 0; $a < $allCoordinatesLength; $a++) {
                    for ($b = 1 + $a; $b < $allCoordinatesLength; $b++) {
                        if ($allCoordinates[$a]['end'] < $allCoordinates[$b]['start']) {
                            if (($allCoordinates[$a]['start'] >= $allCoordinates[$b]['start'] && $allCoordinates[$a]['start'] <= $allCoordinates[$b]['end']) || ($allCoordinates[$a]['end'] >= $allCoordinates[$b]['start'] && $allCoordinates[$a]['end'] <= $allCoordinates[$b]['end'])) {
                                continue;
                            }
                            $textSlice = mb_substr($textCall, $allCoordinates[$a]['end'], $allCoordinates[$b]['start'] - $allCoordinates[$a]['end']);
                            $firstWord = ['start' => $allCoordinates[$a]['start'], 'end' => $allCoordinates[$a]['end']];
                            $secondWord = ['start' => $allCoordinates[$b]['start'], 'end' => $allCoordinates[$b]['end']];
                            if (count(explode(' ', $textSlice)) < 10) {
                                foreach ($map as $key => $item) {
                                    $message_number = $item['id'];
                                    $position = $item['position'];

                                    if ($allCoordinates[$a]['start'] > $position && $map[$key + 1] ? $allCoordinates[$a]['start'] < $map[$key
                                        + 1]['position'] : true
                                    ) {

                                        $this->addMessageTag($marker, [
                                            'id' => $message_number,
                                            'position' => $firstWord,
                                            "channel" => $this->text_channel[$key],
                                            "needchannel" => $needchnnel
                                        ], $color);


                                        for ($indexNext = $key; $indexNext < $mapLength; $indexNext++) {
                                            if ($allCoordinates[$b]['start'] > $position && $map[$indexNext + 1] ? $allCoordinates[$b]['start']
                                                < $map[$indexNext + 1]['position'] : true
                                            ) {
//                                                $markers[$marker]['message_number'][] = $map[$indexNext]['id'];

                                                $this->addMessageTag($marker, [
                                                    'id' => $map[$indexNext]['id'],
                                                    'position' => $secondWord,
                                                    "channel" => $this->text_channel[$key],
                                                    "needchannel" => $needchnnel
                                                ], $color);
                                                break;

                                            }
                                        }
                                        break;
                                    }
                                }

//                                break 2;
                            }
                        }
                    }
                }
            }
        }

        /**
         * TODO: Логика нахождения тэгов
         */
    }

    /**
     * Метод получения тегов
     */
    public function getTags()
    {
        $tags = $this->tags_call;
        foreach ($tags as $marker => $tag) {
            $value = [];
            foreach (array_values($tag['message_number'])  as $val){
                if ( $val["needchannel"] != "all" && $val["needchannel"] != $this->text_channel[$val["id"]]) {
                    /*$value[] = [
                        'color'          => '#ccc',
                        'message_number' => [],
                        'marker'         => $marker
                    ];*/
                }else {
                    $value[] = $val;
                }
            }

            $tags[$marker]['message_number'] = $value;

        }
        return $tags;
    }

    public function getMap()
    {
        return $this->_map;
    }

    public function setMap($map)
    {
        $this->_map = $map;
    }

    private function addTag($title)
    {
        if (is_null($this->tags_call)) {
            $this->tags_call = [];
        }

        if (array_search($title, $this->tags_call) === false) {
            $this->tags_call[$title] = [
                'color' => '#ccc',
                'message_number' => [],
                'marker' => $title
            ];
        }
    }

    private function addMessageTag($marker, $item, $color)
    {
//        if($item['needchnnel'] === $this->text_channel[$item['id']]) {
        $this->tags_call[$marker]['message_number'][$item['position']['start'] . '_' . $item['position']['end']] = $item;
        $this->tags_call[$marker]['color'] = $color;
//        }
    }

    /**
     * @param $tagsArray
     * @param $theme_id
     */
    public function saveReplic($themes, $theme_id, $owner, $eventId)
    {
        if (isset($themes['message_number'])) {
            if (!empty($themes['message_number'])) {
                foreach ($themes['message_number'] as $replic) {
                    $replicNumber = $replic['id'];
                    $query = "INSERT INTO tag_link_" . $owner . " SET tag_id=" . (int)$theme_id . ", event_id=" . (int)$eventId . ", source='call', module='', approve = 1, replic_number =" . (int)$replicNumber;
                    Yii::app()->aidbpr->createCommand($query)->execute();
                }
            } else {
                $query = "INSERT INTO tag_link_" . $owner . " SET tag_id=" . (int)$theme_id . ", event_id=" . (int)$eventId . ", source='call', module='', approve = 1, replic_number =" . -1;
                Yii::app()->aidbpr->createCommand($query)->execute();
            }
        }
    }


    /**
     * @param $tagsArray
     * @param $project_id
     * @return bool
     */
    public function saveThemes($tagsArray)
    {
        try {
            $ownerId = $this->owner_id;
            $eventId = $this->event->id;
            if ($ownerId > 0) {
                $owner = sprintf('%08d', $ownerId);
            } else {
                $owner = sprintf('%08d', 2);
            }
            $theme_id = null;

            if (!empty($tagsArray)) {
                Yii::app()->aidbpr->createCommand()
                    ->delete("tag_link_" . $owner, 'event_id = :event_id', [':event_id' => (int)$eventId]);
                Yii::app()->aidbpr->createCommand()
                    ->delete("tag_color_" . $owner, 'event_id = :event_id', [':event_id' => (int)$eventId]);
                foreach ($tagsArray as $themes) {
                    if (isset($themes["marker"]) && isset($themes['color'])) {
                        $tagTitleNormalized = strtolower(trim($themes["marker"]));
                        $queryi = "SELECT * FROM tags_" . $owner . " WHERE name='" . $themes["marker"] . "'";
                        $getid = Yii::app()->aidbpr->createCommand($queryi)->queryRow();

                        if (!isset($getid['tag_id'])) {
                            try {
                                $query = "INSERT INTO tags_" . $owner . " SET name='" . $themes["marker"] . "', normalize='$tagTitleNormalized', schema_id=" . self::DEFAULT_SCHEME_ID;
                                Yii::app()->aidbpr->createCommand($query)->execute();
                                $theme_id = Yii::app()->aidbpr->getLastInsertID();
                            } catch (Exception $exception) {
                                continue;
                            }
                        } else {
                            $theme_id = $getid['tag_id'];
                        }

                        $color = $themes['color'];
                        if(empty($themes['message_number'])) {
                            $color = '#ccc';
                        }

                        $query = "INSERT INTO tag_color_" . $owner . " SET tag_id='" . $theme_id . "', color='" . $color . "', event_id=" . (int)$eventId;
                        Yii::app()->aidbpr->createCommand($query)->execute();

                        $this->saveReplic($themes, $theme_id, $owner, $eventId);
                    }
                }
            }
        } catch (\Error $e) {
            var_dump($e->getMessage());
            return $e->getMessage();
        }
    }
}
