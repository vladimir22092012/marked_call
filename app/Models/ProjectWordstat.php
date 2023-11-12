<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectWordstat extends Model
{
    use HasFactory;

    /**
     * @return HasMany
     */
    public function types_all(): HasMany
    {
        return $this->hasMany(GoodkeyProjectsOfTypes::class, 'project_id');
    }

    /**
     * @param $word
     * @param $title
     * @param $fromBasis
     * @param $prepareWords
     * @param $color
     * @return void
     */
    public static function addWord($word, $title, $fromBasis, &$prepareWords, $color): void
    {
        $title = trim($title);
        if ($fromBasis === true) {
            if (!isset($prepareWords[$title . '_' . $color])) {
                $prepareWords[$title . '_' . $color] = [];
            }
            $prepareWords[$title . '_' . $color][$word] = true;
        } else {
            $prepareWords[$word] = true;
        }
    }

    /**
     * Возвращает список слов с базисов проекта
     * @return array
     */
    public function getWordsFromColumns($fromBasis = false, $sortSerialNumber = false) {
        $types = $this->types_all;
        $prepareWords = [];

        /**
         * @var GoodkeyProjectsOfTypes $model
         */
        foreach ($types as $index => $model) {
            $columns = json_decode($model->columns, true);

            if($sortSerialNumber === true) {
                usort($columns, function ($a, $b) {
                    return (int)$a['serial_number'] - (int)$b['serial_number'];
                });
            }

            foreach ($columns as $keyColumn => $column) {
                if(in_array($column['title'], ['Минус-слова','минус-слова','Минус слова','минус слова'])) {
                    continue;
                }

                $color = $column['color'];

                // Получаем слова с уровней
                foreach ($column['levels'] as $level) {
                    if ($level[0] !== 'notgroup') {
                        // Базовое слово
                        $level[0] = mb_strtolower($level[0]);
                        if (!empty($level[0])) {
                            self::addWord($level[0], $column['title'], $fromBasis, $prepareWords, $color);
                        }
                    }

                    foreach ($level[1] as $word) {
                        $word[0] = mb_strtolower($word[0]);
                        if (!empty($word[0])) {
                            self::addWord($word[0], $column['title'], $fromBasis, $prepareWords, $color);
                        }
                    }
                }

                // Получаем слова с контента
                $keysb = explode(PHP_EOL, $column['content']);
                foreach ($keysb as $word) {
                    $word = mb_strtolower($word);
                    if(!empty($word)) {
                        self::addWord($word, $column['title'], $fromBasis, $prepareWords, $color);
                    }
                }
            }
        }

        if($fromBasis === true) {
            $words = [];
            foreach ($prepareWords as $title => $pw) {
                // Получаем массив слов
                $words[$title] = array_keys($pw);
                // Фильтруем слова
                $words[$title] = array_filter($words[$title], function ($item) {
                    return stripos($item, '"') === false && stripos($item, '[') === false;
                });
            }
        } else {
            // Получаем массив слов
            $words = array_keys($prepareWords);
            // Фильтруем слова
            $words = array_filter($words, function ($item) {
                return stripos($item, '"') === false && stripos($item, '[') === false;
            });
        }

        return $words;
    }
}
