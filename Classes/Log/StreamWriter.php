<?php
/*
 * Copyright 2021 LABOR.digital
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Last modified: 2021.03.17 at 14:24
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Log;


use LaborDigital\Typo3BetterApi\BetterApiException;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;

/**
 * Class StreamWriter
 *
 * This writer will, by default log into php://stdout which is optimized for docker applications.
 * You can change the output by using the "stream" option, which either supports a url as string, or already connected
 * resource.
 *
 * NOTE: This is writer is a combination of the MonoLog StreamHandler and TYPO3s SyslogWriter
 *
 * @package LaborDigital\Typo3BetterApi\Log
 */
class StreamWriter extends AbstractWriter
{
    /**
     * Either the stream url or the connected resource
     *
     * @var string|resource
     */
    protected $stream;

    /**
     * True if the stream directory was created
     *
     * @var bool
     */
    protected $dirCreated = false;

    /**
     * An error message to catch stream errors
     *
     * @var string|null
     */
    protected $errorMessage;

    /**
     * @inheritDoc
     */
    public function __construct(array $options = [])
    {
        $this->stream = 'php://stdout';
        parent::__construct($options);
    }


    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->closeStream();
    }

    /**
     * Returns the data of the record in syslog format
     *
     * @param   LogRecord  $record
     *
     * @return string
     */
    public function getMessageForSyslog(LogRecord $record)
    {
        $data       = '';
        $recordData = $record->getData();
        if (! empty($recordData)) {
            // According to PSR3 the exception-key may hold an \Exception
            // Since json_encode() does not encode an exception, we run the _toString() here
            if (isset($recordData['exception']) && $recordData['exception'] instanceof \Exception) {
                $recordData['exception'] = (string)$recordData['exception'];
            }
            $data = '- ' . json_encode($recordData);
        }

        return sprintf(
                   '[request="%s" component="%s"] %s %s',
                   $record->getRequestId(),
                   $record->getComponent(),
                   $record->getMessage(),
                   $data
               ) . PHP_EOL;
    }

    /**
     * Allows you to define which stream to connect to
     *
     * @param   string|resource  $stream
     *
     * @return $this
     */
    public function setStream($stream): self
    {
        if ($stream === null) {
            return $this;
        }

        $this->closeStream();

        $this->stream = $stream;

        return $this;
    }

    /**
     * Disconnects the logger from the open resource
     *
     * @return $this
     */
    public function closeStream(): self
    {
        if ($this->stream && is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->stream     = null;
        $this->dirCreated = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function writeLog(LogRecord $record)
    {
        if (! is_resource($this->stream)) {
            if (! is_string($this->stream)) {
                throw new BetterApiException('Could not create a log stream, because the stream is neither a string or valid resource!');
            }

            $this->openStream();
        }

        fwrite($this->stream, $this->getMessageForSyslog($record));
    }

    /**
     * Internal helper to connect a string stream as a stream resource
     *
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     */
    protected function openStream(): void
    {
        if (is_resource($this->stream)) {
            return;
        }

        if (! is_string($this->stream)) {
            throw new BetterApiException(
                'Could not create a log stream, because the stream is neither a url or valid resource!');
        }

        $this->createDir();
        $this->errorMessage = null;
        set_error_handler([$this, 'customErrorHandler']);
        $this->stream = fopen($this->stream, 'ab');
        restore_error_handler();
        if (! is_resource($this->stream)) {
            $this->stream = null;

            throw new \UnexpectedValueException(
                sprintf(
                    'The stream or file "%s" could not be opened in append mode: ' .
                    $this->errorMessage, $this->stream));
        }
    }

    /**
     * @param   string  $stream
     *
     * @return null|string
     */
    protected function getDirFromStream($stream)
    {
        $pos = strpos($stream, '://');
        if ($pos === false) {
            return dirname($stream);
        }

        if ('file://' === substr($stream, 0, 7)) {
            return dirname(substr($stream, 7));
        }

        return null;
    }

    /**
     * Makes sure that the stream directory exists and is writable
     */
    protected function createDir()
    {
        // Do not try to create dir if it has already been tried.
        if ($this->dirCreated || is_resource($this->stream)) {
            return;
        }

        $dir = $this->getDirFromStream($this->stream);
        if (null !== $dir && ! is_dir($dir)) {
            $this->errorMessage = null;
            set_error_handler([$this, 'customErrorHandler']);
            $status = mkdir($dir, 0777, true);
            restore_error_handler();
            /** @noinspection NotOptimalIfConditionsInspection */
            if (false === $status && ! is_dir($dir)) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'There is no existing directory at "%s" and its not buildable: ' . $this->errorMessage, $dir));
            }
        }
        $this->dirCreated = true;
    }

    protected function customErrorHandler($code, $msg)
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }', '', $msg);
    }
}
