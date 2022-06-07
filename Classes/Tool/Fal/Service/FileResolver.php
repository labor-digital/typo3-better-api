<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.06.07 at 21:21
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Fal\Service;


use LaborDigital\T3ba\Tool\Fal\Util\FalFilePathUtil;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Throwable;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;

class FileResolver implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $resourceFactory;
    
    /**
     * @var \TYPO3\CMS\Core\Resource\FileRepository
     */
    protected $fileRepository;
    
    public function __construct(
        ResourceFactory $resourceFactory,
        FileRepository $fileRepository
    )
    {
        $this->resourceFactory = $resourceFactory;
        $this->fileRepository = $fileRepository;
    }
    
    public function resolve($uid, ?string $table = null, ?string $field = null, bool $onlyFirst = true)
    {
        try {
            /** @noinspection ProperNullCoalescingOperatorUsageInspection */
            $data = $this->resolveByRelation($uid, $table, $field) ??
                    $this->resolveByPseudoLinkLabelCombination($uid) ??
                    $this->resolveByQueryParameter($uid) ??
                    $this->resolveAsFileOrFolderObjectWithLegacySupport($uid) ??
                    $this->resolveAsFileOrFolderObject((string)$uid) ??
                    null;
            
            return $this->handleOnlyFirst($data, $onlyFirst);
            
        } catch (Throwable $e) {
            if ($this->logger) {
                $this->logger->error('Failed to resolve file, returning NULL instead', ['exception' => $e, 'uid' => $uid, 'table' => $table, 'field' => $field]);
            }
        }
        
        // @todo this should return either NULL or an empty array if $onlyFirst is set to false
        return null;
    }
    
    /**
     * Tries to find a file by a relation to another table
     *
     * @param   mixed        $uid    The uid of the row in $table and $field
     * @param   string|null  $table  The name of the related table to find the files for
     * @param   string|null  $field  The name of the field for which the references should be found
     *
     * @return array|null
     */
    protected function resolveByRelation($uid, ?string $table = null, ?string $field = null): ?array
    {
        if (! is_numeric($uid) || empty($table) || empty($field)) {
            return null;
        }
        
        return $this->fileRepository->findByRelation(NamingUtil::resolveTableName($table), $field, (int)$uid);
    }
    
    /**
     * Handles a Pseudo Link|Label combination... Oh, gosh TYPO3 is so weired...
     *
     * @param $uid
     *
     * @return \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileInterface|\TYPO3\CMS\Core\Resource\Folder|\TYPO3\CMS\Core\Resource\ProcessedFile|null
     */
    protected function resolveByPseudoLinkLabelCombination($uid)
    {
        if (! is_string($uid) || ! str_contains($uid, '%') || ! str_contains($uid, '|')) {
            return null;
        }
        
        $identifier = explode('|', $uid);
        $identifier = reset($identifier);
        // Crack strange multi-encodings
        for ($i = 0; $i < 25; $i++) {
            $identifier = rawurldecode($identifier);
            if (! str_contains($identifier, '%')) {
                break;
            }
        }
        
        return $this->resolveAsFileOrFolderObject($identifier);
    }
    
    /**
     * Read a query like UID.
     * Note: I think this is no longer supported by the core, but I'm not 100% sure...
     *
     * @param $uid
     *
     * @return \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileInterface|\TYPO3\CMS\Core\Resource\Folder|\TYPO3\CMS\Core\Resource\ProcessedFile|null
     */
    protected function resolveByQueryParameter($uid)
    {
        if (! is_string($uid) || ! str_contains($uid, '=')) {
            return null;
        }
        
        $params = parse_url($uid);
        if (! isset($params['query'])) {
            return null;
        }
        
        parse_str($params['query'], $params);
        if (! isset($params['uid'])) {
            return null;
        }
        
        return $this->resolveAsFileOrFolderObject($params['uid']);
    }
    
    /**
     * Additional layer on top of "resolveAsFileOrFolderObject" to automatically perform a fallback lookup
     * on legacy fal paths (without storage for example)
     *
     * @param $uid
     *
     * @return \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileInterface|\TYPO3\CMS\Core\Resource\Folder|\TYPO3\CMS\Core\Resource\ProcessedFile|null
     */
    protected function resolveAsFileOrFolderObjectWithLegacySupport($uid)
    {
        if (! is_string($uid) || ! str_contains($uid, '/')) {
            return null;
        }
        
        $identifier = FalFilePathUtil::getFalPathArray($uid)['identifier'];
        
        return $this->resolveAsFileOrFolderObject($identifier);
    }
    
    /**
     * Resolves the image through an absolute identifier
     *
     * @param   string  $identifier
     *
     * @return \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileInterface|\TYPO3\CMS\Core\Resource\Folder|\TYPO3\CMS\Core\Resource\ProcessedFile|null
     */
    protected function resolveAsFileOrFolderObject(string $identifier)
    {
        try {
            return $this->resourceFactory->retrieveFileOrFolderObject($identifier);
        } catch (ResourceDoesNotExistException $e) {
            return null;
        }
    }
    
    /**
     * Internal helper to resolve the "onlyFirst" flag based on the resolved data
     *
     * @param         $data
     * @param   bool  $onlyFirst
     *
     * @return array|mixed|null
     */
    protected function handleOnlyFirst($data, bool $onlyFirst)
    {
        if (! $data) {
            return $onlyFirst ? null : [];
        }
        
        if (! is_iterable($data)) {
            $data = [$data];
        }
        
        if ($onlyFirst) {
            /** @noinspection LoopWhichDoesNotLoopInspection */
            foreach ($data as $item) {
                return $item;
            }
        }
        
        return $data;
    }
}