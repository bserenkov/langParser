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
        $fileName = $git->getBranch();
        $csv = new CsvReport($strFilesSet, $fileName);
        $report = $csv->setTmpStorage(getcwd())->export();
        //todo collect debug info instead of /dev/null
        if (is_executable(Config::getInstance()->libreoffice['path']) && is_file($report)) {
            exec(
                sprintf('%s --invisible --convert-to xlsx %s 2>&1 >> /dev/null', Config::getInstance()->libreoffice['path'], $report),
                $output,
                $status
            );
            $reportXls = str_replace('.csv', '.xlsx', $report);
            // if conversion was successful remove temporary csv file
            if (is_file($reportXls)) {
                unlink($report);
                $report = $reportXls;
            } else {
                addMessage('Cannot convert report to .xlsx format. Please use .csv');
            }
        } else {
            addMessage('You dont have libreoffice installed or there is a mistake at your parser.ini');
        }
        addMessage(sprintf("New Report for JIRA generated locally at %s. (y - proceed with JIRA ticket creation,n - stop execution):", $report)
        );
        foreach (stdin_stream() as $line) {
            $line = trim($line);
            if ($line === 'y') {
                addMessage('Not implemented yet. doh!');
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