#!/usr/bin/env php
<?php

array_shift($argv);

$cli = $argv;

if (empty($cli)) {
    foreach (glob('/usr/bin/php*') as $path) {
        $cli[] = $path;
    }
}

$cli = array_unique($cli);
$php = array();

foreach ($cli as $bin) {
    $path = realpath($bin);

    if (!$path) {
        $which = trim(exec('which ' . escapeshellarg($bin)));

        if ($which) {
            $path = realpath($which);
        }
    }

    if (!$path) {
        fputs(STDERR, sprintf("Can't find path to %s\n", $bin));
        continue;
    }

    $version = exec(escapeshellcmd($path) . ' -r \'echo PHP_VERSION;\'');

    if (preg_match('/^(\d+\.\d+\.\d+)/', $version, $matches)) {
        $version = $matches[1];
        $php[$version] = $path;
    } else {
        fputs(STDERR, sprintf("Can't get PHP version for %s, ignored.\n", $bin));
    }
}

if (!$php) {
    fputs(STDERR, "There are no valid php binaries... :(\n");
    exit(-1);
}

ksort($php);

$results = array();
$tests = array();
$benchmark = __DIR__ . '/benchmark.php';

foreach ($php as $version => $bin) {
    fputs(STDERR, sprintf("Testing PHP %s ... ", $version));
    $output = shell_exec(escapeshellcmd($bin) . ' ' . escapeshellarg($benchmark) . ' | sed -E \'1,2d;/^-+/,+2d\'');
    fputs(STDERR, "done\n");

    foreach (explode("\n", $output) as $line) {
        if (!$line) {
            continue;
        }

        list($test, $score, $units) = preg_split('/\s{2,}/', $line);

        $test = trim($test);

        $results[$version][$test] = trim($score);

        if (!isset($tests[$test])) {
            $tests[$test] = trim($units);
        }
    }
}

$fs = ":";

$cpu = exec('grep -i "model name" /proc/cpuinfo | head -n1');
$cpu = trim(preg_replace('/^[^:]*:/', '', $cpu));

echo $cpu ? $cpu : 'Test', $fs, 'Units', $fs;

foreach ($php as $version => $bin) {
    echo 'PHP ', $version, $fs;
}

echo PHP_EOL;

foreach ($tests as $test => $units) {
    echo $test, $fs, $units, $fs;

    foreach ($php as $version => $bin) {
        echo $results[$version][$test], $fs;
    }

    echo PHP_EOL;
}