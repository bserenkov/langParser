<?php

/**
 * Class CsvExport
 */
class CsvReport
{

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var string
     */
    protected $tmpStorage;

    /**
     * @return string
     */
    public function getTmpStorage()
    {
        return $this->tmpStorage;
    }

    /**
     * @param string $tmpStorage
     * @return $this;
     */
    public function setTmpStorage($tmpStorage)
    {
        $this->tmpStorage = $tmpStorage;
        return $this;
    }

    /**
     * CsvReport constructor.
     * @param array $gitData
     * @param null $filename
     */
    public function __construct(array $gitData, $filename = null)
    {
        $this->data = $gitData;
        $this->filename = ($filename ? $filename: uniqid()) . '.csv';
        $this->delimiter = Config::getInstance()->csv['delimiter'];
    }


    /**
     * @param bool $quite
     * @return string
     * @throws Exception
     */
    public function export($quite = true)
    {
        $path = $this->getTmpFileStorage();
        $handler = fopen($path, 'w');
        if (!is_resource($handler)) {
            throw new \Exception(sprintf('Cannot store temporary report at %s. Please check permissions', $path));
        }
        $setupHeader = $this->generateHeader($handler);
        if (!$setupHeader) {
            throw new \Exception('Cannot write a header at report.Please check your permissions');
        }
        foreach($this->data as $fileName => $strFileLabels) {
            fputcsv($handler, [$fileName], $this->getDelimiter());
            foreach ($strFileLabels as $key => $label) {
                fputcsv($handler, [$key, $label, ' '], $this->getDelimiter());
            }
        }
        return $path;
    }

    /**
     * @return string
     */
    protected function getTmpFileStorage()
    {
        $storage = $this->tmpStorage;
        $folder = null === $storage ? sys_get_temp_dir() : $storage;
        return $folder . DIRECTORY_SEPARATOR . $this->getFilename();
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return $this;
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @param $descriptor
     * @return int
     */
    protected function generateHeader($descriptor)
    {
        return fputcsv($descriptor, array('Internal Name', 'English', 'French'), $this->getDelimiter());
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     * @return $this;
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }


}