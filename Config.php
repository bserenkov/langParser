<?php


class Config
{

    private static $instance;

    private $conf = [];

    public function __get($name)
    {
        if (isset($this->conf[$name])) {
            return $this->conf[$name];
        }
        throw new \Exception(sprintf('Unknown config option %s', $name));
    }


    private function __construct()
    {
        $iniFile = ROOT . DIRECTORY_SEPARATOR . 'parser.ini';
        if (!is_file($iniFile)) {
            throw new \Exception('Cannot find parser.ini file. Please create it first at ' . ROOT);
        }
        $this->conf = parse_ini_file($iniFile, true);
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof Config) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}