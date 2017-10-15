<?php

/**
 * Created by PhpStorm.
 * User: mkardakov
 * Date: 7/8/17
 * Time: 9:15 PM
 */
class Git
{

    /**
     * @return bool|string
     */
    public function getBranch()
    {
        static $branch;
        if (is_null($branch)) {
            $branch = $this->exec('git branch | grep -oP "(?<=\* ).*"');
        }
        return isset($branch[0]) ? $branch[0] : false;
    }

    /**
     * @param string $remote
     * @param string $alias
     * @return array
     */
    public function getStrFileDiff($remote, $alias = 'origin')
    {
        $currentBranch = $this->getBranch();
        $diff = $this->exec(sprintf('git diff %s/%s..%1$s/%s --name-only', $alias, $currentBranch, $remote));
        $result = [];
        $strFilesSet = array_filter($diff, function ($fileName) {
            return preg_match('/en\/(?:str_|main\.inc)/', $fileName);
        });
        foreach ($strFilesSet as $file) {
            $lineDiff = $this->exec(sprintf('git diff %s/%s..%1$s/%s -- %s', $alias, $currentBranch, $remote, $file), true);
            if (!empty($lineDiff)) {
                $parse = $this->grepLabelsDiff($lineDiff);
                if (!empty($parse)) {
                    $result[$file] = $parse;
                }
            }
        }
        return $result;
    }

    /**
     * @param $command
     * @param bool $plainText
     * @return string
     * @throws Exception
     */
    private function exec($command, $plainText = false)
    {
        $status = 0;
        $plainText ? ($output = shell_exec($command)) : exec($command, $output, $status);
        if ($status > 0) {
            throw new \Exception(implode(PHP_EOL, $output));
        }
        return $output;
    }

    /**
     * Accepts diff of 2 file versions and parses VarName/Value
     * @param $str
     * @return array
     */
    private function grepLabelsDiff($str)
    {
        $result = [];
        preg_match_all('/^\+\s*\$(?<name>[^=]+)=\s*[\'"](?<value>.*);\s*$/im', $str, $matches);
        if (isset($matches['name'], $matches['value'])) {
            $result = array_combine($matches['name'], $matches['value']);
            array_walk($result, function (&$value) {
                $value = trim($value, '"\' ');
            });
        }
        return $result;
    }
}