<?php

define('ROOT', __DIR__);
require ROOT . DIRECTORY_SEPARATOR . 'Git.php';
require ROOT . DIRECTORY_SEPARATOR . 'CsvReport.php';
require ROOT . DIRECTORY_SEPARATOR . 'Config.php';

try {
    if (!isset($argv[1]) || !preg_match(Config::getInstance()->git['input_branch_pattern'], $argv[1])) {
        throw new \Exception(
            sprintf(
                'Invalid branch name "%s" to compare. Branch name must satisfy branch name pattern %s',
                isset($argv[1]) ? $argv[1] : 'none',
                Config::getInstance()->git['input_branch_pattern']
            )
        );
    }
    $git = new Git();
    $strFilesSet = $git->getStrFileDiff($argv[1]);
    if (!empty($strFilesSet)) {
        $csv = new CsvReport($strFilesSet);
        $report = $csv->setTmpStorage('.')->export();
        addMessage(
            sprintf("%sNew Csv Report for JIRA generated locally at %s. (y - proceed with JIRA ticket creation,n - stop execution):",
                PHP_EOL,
                $report)
        );
        foreach (stdin_stream() as $line) {
            $line = trim($line);
            if ($line === 'y') {
                break;
            } elseif ($line === 'n') {
                die('stop');
            } else {
                echo $readMessage;
            }
        }

    } else {
        addMessage("No changes detected at language files!");
    }
} catch (\Exception $e) {
    print_r($e);
    exit(1);
}

function stdin_stream()
{
    while ($line = fgets(STDIN)) {
        yield $line;
    }
}

function addMessage($text, $color = null)
{
    echo PHP_EOL . $text . PHP_EOL;
}
?>