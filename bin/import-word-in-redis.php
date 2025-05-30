<?php

require __DIR__ . '/../vendor/autoload.php';

use Predis\Client;

$redis = new Client([
    'host' => 'redis',
    'port' => 6379,
]);

$file = __DIR__ . '/../data/russian_nouns.txt';

$handle = fopen($file, 'r');
if (!$handle) {
    exit("Не удалось открыть файл.\n");
}

while (($word = fgets($handle)) !== false) {
    $word = trim(mb_strtolower($word));
    if ($word === '') continue;

    $firstLetter = mb_substr($word, 0, 1);
    $redis->sadd("words:$firstLetter", $word);

    // Если слово длиннее 13 символов, добавляем в отдельный набор
    if (mb_strlen($word) > 13) {
        $redis->sadd('words:long', $word);
    }
}

fclose($handle);

echo "Импорт завершён.\n";
