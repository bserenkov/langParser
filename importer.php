<?php

define('ROOT', __DIR__);
require ROOT . DIRECTORY_SEPARATOR . 'Git.php';
require ROOT . DIRECTORY_SEPARATOR . 'CsvReport.php';
require ROOT . DIRECTORY_SEPARATOR . 'Config.php';

try {
    if (!isset($argv[1]) || !preg_match('/\.(csv|xlsx?)$/i', $argv[1], $match)) {
        addMessage(
            sprintf(
                'Only .csv and .xlsx files are accepted',
                isset($argv[1]) ? $argv[1] : 'none',
                Config::getInstance()->git['input_branch_pattern']
            )
        );
        exit(1);
    }
    define('EXTENSION', $match[1]);
    $inputFile = realpath($argv[1]);
    switch (EXTENSION) {
        case 'xls':
        case 'xlsx':
            if (is_executable(Config::getInstance()->libreoffice['path'])) {
                exec(
                    sprintf('%s --invisible --convert-to csv %s 2>&1 >> /dev/null', Config::getInstance()->libreoffice['path'], $inputFile),
                    $output,
                    $status
                );
                $inputFile = str_replace("." . EXTENSION, '.csv', $inputFile);
            } else {
                addMessage('You dont have libreoffice installed or there is a mistake at your parser.ini');
            }
        case 'csv':
            if (!is_file($inputFile)) {
                addMessage(sprintf('File %s does not exist or there was a problem during conversion', $inputFile));
                exit(1);
            }
            importCsv($inputFile);
            break;
        default:
            addMessage(sprintf('Unsupported extension %s', EXTENSION));
            exit(1);
    }

} catch (\Exception $e) {
    print_r($e);
    exit(1);
}


function addMessage($text, $color = null)
{
    echo PHP_EOL . $text . PHP_EOL;
}

/**
 * @param $file csv file import sheet.It generated previously by exporter.php
 * @todo function is harcoded for eng to french translation
 * @return void
 */
function importCsv($file)
{
    $handler = fopen($file, 'r');
    // move pointer behind csv header
    fgetcsv($handler);
    $enFile = $frFile = null;
    // read safe csv string
    while ($line = getCsvLine($handler)) {
        if (isset($line[0]) && is_file(getcwd() . DIRECTORY_SEPARATOR . $line[0])) {
            $enFile = getcwd() . DIRECTORY_SEPARATOR . $line[0];
            $frFile = str_replace('en/', 'fr/', $enFile);
            if (!is_file($enFile)) {
                addMessage(sprintf('Error: Base English file file not found. "%s". Force stop..', $enFile));
                break;
            }
            // if french translation file is not created yet
            if (!is_file($frFile)) {
                file_put_contents($frFile, '<?php'  . PHP_EOL);
                addMessage(sprintf('Info: New French file was created: "%s"', $frFile));
            }
            continue;
        }
        if (!empty($line[0]) && !empty($line[2])) {
            // language variable key to be matched by regex
            $replaceRegEx = '/^(\s*)\$' . preg_quote($line[0]) . '.+/m';
            // replace string
            $toInsert = sprintf('$%s = "%s";', $line[0], escapeTranslation($line[2]));
            $strFileContent = file_get_contents($frFile);
            // if variable key is already exist it needs to be replaced on the same position
            if (preg_match($replaceRegEx, $strFileContent, $tabsMatches)) {
                $strFileContent = preg_replace($replaceRegEx, $tabsMatches[1] . $toInsert, $strFileContent);
                file_put_contents($frFile, $strFileContent);
            // If not than append new translation at the end of the str_ file
            } else {
                // if Close php tag not found \?\>
                if (false === ($closeTag = strrpos($strFileContent, '?>'))) {
                    file_put_contents($frFile, $toInsert . PHP_EOL, FILE_APPEND);
                } else {
                    $strFileContent = preg_replace('/(\?>\s*)/', $toInsert . PHP_EOL . '$1', $strFileContent);
                    // encoding issue found
                    file_put_contents($frFile, $strFileContent);
                }
            }
        }

    }
}

/**
 * @param resource $handler
 * @return array|false
 */
function getCsvLine($handler)
{
    $readLine = fgetcsv($handler, 0, Config::getInstance()->csv['delimiter']);
    if (is_array($readLine)) {
        array_walk($readLine, function (string &$value) {
            if (EXTENSION === 'xls' || EXTENSION === 'xlsx') {
                $value = mb_convert_encoding($value, 'UTF-8', 'CP1252');
            }
            $value = trim($value, '"\' ');
        });
    }
    return $readLine;
}

/**
 * Process and escape variables inside of the translations
 * @todo add support construction like $strtable = $strRecords = 'value'
 * @param string $value
 * @return string
 */
function escapeTranslation(string $value): string
{
    return $value;
}

?>