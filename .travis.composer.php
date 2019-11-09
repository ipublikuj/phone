<?php

$version = getenv('NETTE');

if (!$version || $version == 'default') {
	exit;
}

echo "Nette version " . $version . PHP_EOL;

$file = __DIR__ . '/composer.json';

$content = file_get_contents($file);

$composer = json_decode($content, TRUE);

$composer['require']['nette/di'] = $version;

$content = json_encode($composer);

file_put_contents($file, $content);