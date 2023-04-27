<?php
/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_DbDumper
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 */

declare(strict_types=1);

namespace Nanobots\DbDumper\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;

class FileWriter
{
    /** @var string  */
    private string $_file = '';

    /** @var int|null  */
    private ?int $_timeStamp = null;

    /** @var DirectoryList  */
    protected DirectoryList $directoryList;

    /** @var FileIo  */
    protected FileIo $fileIo;

    /** @var File  */
    protected File $fileDriver;

    /**
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param FileIo $fileIo
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     */
    public function __construct(
        DirectoryList $directoryList,
        FileIo $fileIo,
        File $fileDriver
    ) {
        $this->directoryList = $directoryList;
        $this->fileIo = $fileIo;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @param string $fileContent
     * @param int|null $mode
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function writeToFile(string $fileContent = '', ?int $mode = null): int
    {
        $varPath = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $fileName = sprintf('%s/%s', $varPath, $this->getFile());
        $destinationFolder = $this->fileIo->getDestinationFolder($fileName);

        try {
            if ($this->fileIo->checkAndCreateFolder($destinationFolder)) {
                return $this->fileDriver->filePutContents($fileName, $fileContent, $mode);
            }
        } catch (LocalizedException|FileSystemException $e) {
            throw new FileSystemException(
                __(
                    'There was a problem with writing to file %1, error: %2',
                    $fileName,
                    $e->getMessage()
                )
            );
        }

        return 0;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile(string $file): self
    {
        $this->_file = $file;

        return $this;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->_file;
    }

    /**
     * @return int|null
     */
    public function getTimeStamp(): ?int
    {
        return $this->_timeStamp;
    }

    /**
     * @param int|null $timeStamp
     * @return $this
     */
    public function setTimeStamp(?int $timeStamp): self
    {
        $this->_timeStamp = $timeStamp;

        return $this;
    }
}
