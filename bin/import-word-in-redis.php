<?php

require __DIR__ . '/../vendor/autoload.php';

use Predis\Client;

function showProgress($current, $total, $startTime)
{
    static $spinner = ['⠏', '⠛', '⠹', '⠼', '⠶', '⠧'];
    static $spinnerPos = 0;

    $percent = ($total > 0) ? round(($current / $total) * 100) : 0;
    $elapsed = time() - $startTime;

    // Форматируем прогресс-бар
    $progressBar = str_repeat('=', (int)($percent / 2)) . '>';
    $progressBar = str_pad($progressBar, 50, ' ', STR_PAD_RIGHT);

    echo sprintf(
        "%s [%s] %d/%d (%d%%) %ds\r",
        $spinner[$spinnerPos % count($spinner)],
        $progressBar,
        $current,
        $total,
        $percent,
        $elapsed
    );

    $spinnerPos++;
}

$redis = new Client([
    'host' => 'redis',
    'port' => 6379,
]);

$file = __DIR__ . '/../data/russian_nouns.txt';

// Считаем общее количество строк для прогресса
$totalLines = count(file($file));
$processed = 0;
$startTime = time();

echo "Начало импорта...\n";

$handle = fopen($file, 'r');
if (!$handle) {
    exit("Не удалось открыть файл.\n");
}

while (($word = fgets($handle)) !== false) {
    $word = trim(mb_strtolower($word));
    if ($word === '') {
        continue;
    }

    $firstLetter = mb_substr($word, 0, 1);
    $redis->sadd("words:$firstLetter", $word);

    if (mb_strlen($word) > 13) {
        $redis->sadd('words:long', $word);
    }

    $processed++;
    if ($processed % 100 === 0) { // Обновляем индикатор каждые 100 слов
        showProgress($processed, $totalLines, $startTime);
    }
}

fclose($handle);

// Финал
showProgress($processed, $totalLines, $startTime);
echo "\nИмпорт завершён. Обработано слов: $processed\n";
