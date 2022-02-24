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
 * Last modified: 2022.02.24 at 14:25
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Fal\Service;


use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\Fal\FalException;
use LaborDigital\T3ba\Tool\Fal\Util\DataHandlerAdapter;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Core\DataHandling\ReferenceIndexUpdater;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileReferenceCreator implements SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Core\DataHandling\ReferenceIndexUpdater
     */
    protected ReferenceIndexUpdater $referenceIndexUpdater;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected TypoContext $typoContext;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Database\DbService
     */
    protected DbService $dbService;
    
    /**
     * @var FileInterface
     */
    protected $file;
    
    /**
     * @var array
     */
    protected $row;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery
     */
    protected $tableQuery;
    
    /**
     * @var string
     */
    protected $tableName;
    
    /**
     * @var string
     */
    protected $fieldName;
    
    public function __construct(
        ReferenceIndexUpdater $referenceIndexUpdater,
        TypoContext $typoContext,
        DbService $dbService
    )
    {
        $this->referenceIndexUpdater = $referenceIndexUpdater;
        $this->typoContext = $typoContext;
        $this->dbService = $dbService;
    }
    
    /**
     * Creates a new file reference based on a file and the record information where it should be attached to.
     *
     * @param   \TYPO3\CMS\Core\Resource\FileInterface  $file
     * @param   int                                     $uid
     * @param   string                                  $fieldName
     * @param   string                                  $tableName
     *
     * @return int Returns the uid of the newly created file reference
     *
     * @throws \LaborDigital\T3ba\Tool\Fal\FalException
     * @see \LaborDigital\T3ba\Tool\Fal\FalService::addFileReference()
     */
    public function createFileReference(
        FileInterface $file,
        int $uid,
        string $fieldName,
        string $tableName
    ): int
    {
        try {
            $this->file = $file;
            $this->fieldName = $fieldName ?? 'image';
            $this->tableName = NamingUtil::resolveTableName($tableName ?? 'tt_content');
            
            $this->tableQuery = $this->dbService->getQuery($tableName)
                                                ->withIncludeHidden()
                                                ->withWhere(['uid' => $uid]);
            
            $this->row = $this->tableQuery->getFirst();
            
            if (empty($this->row)) {
                throw new FalException(
                    'There is no record (' . $tableName . ') with uid: ' . $uid . ' to create a file reference for');
            }
            
            return TcaUtil::runWithResolvedTypeTca($this->row, $this->tableName, function (array $tca) {
                return $this->handleCreationForType($tca);
            });
        } finally {
            $this->reset();
        }
    }
    
    /**
     * Executed inside the TcaUtil::runWithResolvedTypeTca wrap in order to run with all tca types in place
     *
     * @param   array  $tca
     *
     * @return int
     * @throws \LaborDigital\T3ba\Tool\Fal\FalException
     */
    protected function handleCreationForType(array $tca): int
    {
        $fieldTca = $tca['columns'][$this->fieldName] ?? null;
        
        if (! is_array($fieldTca) ||
            ($fieldTca['config']['type'] ?? null) !== 'inline' ||
            ($fieldTca['config']['foreign_table'] ?? null) !== 'sys_file_reference') {
            throw new FalException(
                'The field "' . $this->fieldName . '" in table: "' . $this->tableName .
                '" is not configured as "inline" field for "sys_file_reference".');
        }
        
        $existingRelations = $this->findExistingRelations($fieldTca);
        $newRelationUid = $this->createNewFileReferenceRow();
        
        $newRelations = array_merge([$newRelationUid], $existingRelations);
        $newRelations = DataHandlerAdapter::applyFilters($fieldTca, $newRelations);
        
        $this->persistNewRelations($fieldTca, $newRelations);
        
        return $newRelationUid;
    }
    
    /**
     * Resolves the existing relations for the current field as an array
     *
     * @param   array  $fieldTca
     *
     * @return array
     */
    protected function findExistingRelations(array $fieldTca): array
    {
        $handler = $this->createRelationHandlerInstance();
        $handler->start(
            $this->row[$this->fieldName] ?? null,
            'sys_file_reference',
            $fieldTca['config']['MM'] ?? '',
            $this->row['uid'],
            $this->tableName,
            $fieldTca['config'] ?? []
        );
        
        return $handler->tableArray['sys_file_reference'] ?? [];
    }
    
    /**
     * Creates a new row in the sys_file_reference table and returns it's uid
     *
     * @return int
     */
    protected function createNewFileReferenceRow(): int
    {
        return $this->dbService->getQuery('sys_file_reference')->insert([
            'table_local' => 'sys_file',
            'sys_language_uid' => TcaUtil::getLanguageUid($this->row, $this->tableName) ?? 0,
            'tstamp' => $GLOBALS['EXEC_TIME'],
            'crdate' => $GLOBALS['EXEC_TIME'],
            'cruser_id' => 0,
            'l10n_parent' => 0,
            'uid_local' => $this->file->getProperty('uid'),
            'tablenames' => $this->tableName,
            'uid_foreign' => $this->row['uid'],
            'fieldname' => $this->fieldName,
            'pid' => $this->row['pid'],
        ], true);
    }
    
    /**
     * Takes both the field's config array and the list of relation uids to persist.
     * It applies the sorting, updates the foreign field and timestamp
     *
     * @param   array  $fieldTca
     * @param   array  $newRelations
     *
     * @return void
     */
    protected function persistNewRelations(array $fieldTca, array $newRelations): void
    {
        $handler = $this->createRelationHandlerInstance();
        $handler->start(
            implode(',', $newRelations),
            'sys_file_reference',
            $fieldTca['config']['MM'] ?? '',
            $this->row['uid'],
            $this->tableName,
            $fieldTca['config'] ?? []);
        $handler->processDeletePlaceholder();
        $handler->writeForeignField($fieldTca['config'] ?? [], $this->row['uid'], 0, false);
        
        $fieldArray = [
            $this->fieldName => $handler->countItems(false),
        ];
        
        if (is_string($GLOBALS['TCA'][$this->tableName]['ctrl']['tstamp'] ?? null)) {
            $fieldArray[$GLOBALS['TCA'][$this->tableName]['ctrl']['tstamp']] = $GLOBALS['EXEC_TIME'];
        }
        
        $this->tableQuery->update($fieldArray);
    }
    
    /**
     * Resets the local references after the creation is complete
     *
     * @return void
     */
    protected function reset(): void
    {
        unset($this->row, $this->tableName, $this->tableQuery, $this->fieldName, $this->file);
    }
    
    /**
     * Copied from data handler to use a relation handler prepared for the current user if possible
     *
     * @return RelationHandler
     * @see \TYPO3\CMS\Core\DataHandling\DataHandler::createRelationHandlerInstance()
     */
    protected function createRelationHandlerInstance(): RelationHandler
    {
        $isWorkspacesLoaded = ExtensionManagementUtility::isLoaded('workspaces');
        $relationHandler = GeneralUtility::makeInstance(RelationHandler::class);
        if ($this->typoContext->beUser()->isLoggedIn()) {
            $relationHandler->setWorkspaceId($this->typoContext->beUser()->getUser()->workspace);
            $relationHandler->setUseLiveReferenceIds($isWorkspacesLoaded);
            $relationHandler->setUseLiveParentIds($isWorkspacesLoaded);
        }
        $relationHandler->setReferenceIndexUpdater($this->referenceIndexUpdater);
        
        return $relationHandler;
    }
}