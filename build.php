<?php

define('SUPPORTED_ARGUMENTS', ['importer', 'exporter']);
define('SUPPORTED_ARGUMENTS_PATTERN', '/^(' . implode('|', SUPPORTED_ARGUMENTS) .')$/i');

if (empty($argv[1]) || !preg_match(SUPPORTED_ARGUMENTS_PATTERN, $argv[1])) {
    die(PHP_EOL . 'build name required. To Build correctly use: php ' . __FILE__ . ' [importer,exporter,...]' . PHP_EOL);
}
$phar = new Phar($argv[1] .'.phar', 0, $argv[1]);
// just required when modifiyng stub
$phar->startBuffering();
$phar->buildFromDirectory(__DIR__, '/\.(php|ini)$/');
$stub = "#!/usr/bin/env php \n" . $phar->createDefaultStub($argv[1] . '.php');
$phar->setStub($stub);

$phar->stopBuffering();
?>