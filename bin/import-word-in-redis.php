<?php

require __DIR__ . '/../vendor/autoload.php';

use Predis\Client;

function showProgress($current, $total, $startTime)
{
    static $spinner = ['⠏', '⠛', '⠹', '⠼', '⠶', '⠧'];
    static $spinnerPos = 0;

    $percent = ($total > 0) ? round(($current / $total) * 100) : 0;
    $elapsed = time() - $startTime;

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

/**
 * Импортирует слова из файла в Redis
 */
function importWords(string $file, string $alias, Client $redis)
{
    if (!file_exists($file)) {
        echo "Файл не найден: $file\n";
        return;
    }

    echo "Импорт файла: $file (alias: $alias)\n";

    $totalLines = count(file($file));
    $processed = 0;
    $startTime = time();

    $handle = fopen($file, 'r');
    if (!$handle) {
        echo "Не удалось открыть файл $file\n";
        return;
    }

    while (($word = fgets($handle)) !== false) {
        $word = trim(mb_strtolower($word));
        if ($word === '') {
            continue;
        }

        $firstLetter = mb_substr($word, 0, 1);
        $redis->sadd("words:$alias:$firstLetter", $word);

        if (mb_strlen($word) > 13) {
            $redis->sadd("words:$alias:long", $word);
        }

        $processed++;
        if ($processed % 100 === 0) {
            showProgress($processed, $totalLines, $startTime);
        }
    }

    fclose($handle);
    showProgress($processed, $totalLines, $startTime);
    echo "\nИмпорт завершён. Обработано слов: $processed\n\n";
}


$args = $argv;
array_shift($args);

if (count($args) === 0) {
    echo "Использование:\n";
    echo "  php import_words.php file1.txt -a ru [file2.txt -a en ...]\n\n";
    exit(1);
}

$files = [];
$currentFile = null;

for ($i = 0; $i < count($args); $i++) {
    if ($args[$i] === '-a' || $args[$i] === '--alias') {
        if ($currentFile === null) {
            echo "Ошибка: перед -a нужно указать файл.\n";
            exit(1);
        }
        $alias = $args[++$i] ?? null;
        if (!$alias) {
            echo "Ошибка: после -a нужно указать псевдоним.\n";
            exit(1);
        }
        $files[] = ['file' => $currentFile, 'alias' => $alias];
        $currentFile = null;
    } else {
        $currentFile = $args[$i];
    }
}

if ($currentFile !== null) {
    echo "Ошибка: после файла нужно указать -a <alias>.\n";
    exit(1);
}

$redis = new Client([
    'host' => 'redis',
    'port' => 6379,
]);
$redis->select(1);

foreach ($files as $pair) {
    importWords($pair['file'], $pair['alias'], $redis);
}
