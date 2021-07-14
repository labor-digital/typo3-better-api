<?php
/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;

use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedHiddenRestriction;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\VisibilityAspect;
use TYPO3\CMS\Extbase\Persistence\Generic\Backend;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Selector;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class BetterVisibilityAspect extends VisibilityAspect implements AspectInterface
{
    use AutomaticAspectGetTrait;

    /**
     * @var TypoContext
     */
    protected $context;

    /**
     * A list of table names that are allowed to be retrieved, even if they are hidden
     *
     * @var array
     */
    protected $includeHiddenOfTables = [];

    protected $includeHiddenTablesListenerRegistered = false;

    /**
     * Inject the typo context instance
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $context
     */
    public function injectContext(TypoContext $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name)
    {
        return $this->handleGet($name);
    }

    /**
     * @param   bool  $includeHiddenPages
     *
     * @return BetterVisibilityAspect
     */
    public function setIncludeHiddenPages(bool $includeHiddenPages): BetterVisibilityAspect
    {
        $this->getRootVisibilityAspect()->includeHiddenPages = $includeHiddenPages;

        return $this;
    }

    /**
     * @param   bool  $includeHiddenContent
     *
     * @return BetterVisibilityAspect
     */
    public function setIncludeHiddenContent(bool $includeHiddenContent): BetterVisibilityAspect
    {
        $this->getRootVisibilityAspect()->includeHiddenContent = $includeHiddenContent;

        return $this;
    }

    /**
     * @param   bool  $includeDeletedRecords
     *
     * @return BetterVisibilityAspect
     */
    public function setIncludeDeletedRecords(bool $includeDeletedRecords): BetterVisibilityAspect
    {
        $this->getRootVisibilityAspect()->includeDeletedRecords = $includeDeletedRecords;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function includeHiddenPages(): bool
    {
        return $this->getRootVisibilityAspect()->includeHiddenPages();
    }

    /**
     * @inheritDoc
     */
    public function includeHiddenContent(): bool
    {
        return $this->getRootVisibilityAspect()->includeHiddenContent();
    }

    /**
     * @inheritDoc
     */
    public function includeDeletedRecords(): bool
    {
        return $this->getRootVisibilityAspect()->includeDeletedRecords();
    }

    /**
     * Returns the list of tables that are allowed to show hidden records
     *
     * @return array
     */
    public function getIncludeHiddenOfTables(): array
    {
        return $this->includeHiddenOfTables;
    }

    /**
     * Returns true if the given table name is in the list of tables which are allowed to show their hidden records.
     *
     * @param   string  $tableName
     *
     * @return bool
     */
    public function includeHiddenOfTable(string $tableName): bool
    {
        return in_array($tableName, $this->includeHiddenOfTables, true);
    }

    /**
     * Sets the list of tables that are allowed to show their hidden contents
     *
     * @param   array  $includeHiddenOfTables
     *
     * @return BetterVisibilityAspect
     */
    public function setIncludeHiddenOfTables(array $includeHiddenOfTables): BetterVisibilityAspect
    {
        $this->includeHiddenOfTables = $includeHiddenOfTables;

        $this->registerIncludeHiddenTablesEventHandler();

        return $this;
    }

    /**
     * Registers the required event handlers and hooks to apply the hidden table override at the required locations.
     */
    protected function registerIncludeHiddenTablesEventHandler(): void
    {
        if ($this->includeHiddenTablesListenerRegistered) {
            return;
        }
        $this->includeHiddenTablesListenerRegistered = true;

        ExtendedHiddenRestriction::$hooks[static::class] = function (array &$tables) {
            $tables = array_filter($tables, function (string $tableName) {
                return ! $this->includeHiddenOfTable($tableName);
            });
        };

        TypoEventBus::getInstance()->addListener(Backend::class . '.beforeGettingObjectData', function (
            QueryInterface $query
        ) {
            $source = $query->getSource();
            if (! $source instanceof Selector) {
                return;
            }

            if (! $this->includeHiddenOfTable($source->getSelectorName())) {
                return;
            }

            $settings = $query->getQuerySettings();
            $settings->setIgnoreEnableFields(true);
            $settings->setEnableFieldsToBeIgnored(['disabled']);
        });

    }

    /**
     * Returns the root context's visibility aspect
     *
     * @return \TYPO3\CMS\Core\Context\VisibilityAspect|mixed
     */
    public function getRootVisibilityAspect(): VisibilityAspect
    {
        return $this->context->getRootContext()->getAspect('visibility');
    }
}
