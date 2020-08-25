<?php namespace LaborDigital\Typo3BetterApi\Cache\Internals;

interface ExtendedSimpleFileBackendInterface
{
    /**
     * Returns the filename for a cache key stored in this cache's directory
     *
     * @param   string  $key  The key to look up
     *
     * @return mixed Either the filepath or false if no file was found for this key
     */
    public function getFilenameForKey(string $key);
    
    /**
     * Returns the directory where the cache files are stored
     *
     * @return string Full path of the cache directory
     * @api
     */
    public function getCacheDirectory();
}
