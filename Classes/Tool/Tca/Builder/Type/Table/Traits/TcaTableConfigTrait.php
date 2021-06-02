<?php /*
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
 * Last modified: 2021.06.02 at 14:09
 */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Traits;


use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\CshLabelStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\DomainModelMapStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\ListPositionStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\TablesOnStandardPagesStep;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

trait TcaTableConfigTrait
{
    /**
     * Returns true if the records of this table should be manually sortable in the backend
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return is_string($this->config['ctrl']['sortby'] ?? null);
    }
    
    /**
     * Allows you to define if the table rows should be manually sortable in the backend
     *
     * @param   bool  $sortable
     *
     * @return $this
     */
    public function setSortable(bool $sortable = true)
    {
        if ($sortable) {
            $this->config['ctrl']['sortby'] = 'sorting';
        } else {
            unset($this->config['ctrl']['sortby']);
        }
        
        return $this;
    }
    
    /**
     * Returns true if this table is hidden in record listings, especially the list module
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return (bool)($this->config['ctrl']['hideTable'] ?? true);
    }
    
    /**
     * Allows you to define if this table is hidden in record listings, especially the list module
     *
     * @param   bool  $hidden
     *
     * @return $this
     */
    public function setHidden(bool $hidden = true)
    {
        $this->config['ctrl']['hideTable'] = $hidden;
        
        return $this;
    }
    
    /**
     * Allows you to define the database column to use for sorting the elements.
     * With this the records are considered manually sortable.
     *
     * The default value for this column is "sorting"
     *
     * NOTE: If you use $this->setSortable() This field is automatically configured
     *
     * The field contains an integer value which positions it at the correct position between other records from the
     * same table on the current page.
     *
     * This feature is used by e.g. the “pages” table and “tt_content” table (Content Elements) in order to output the
     * pages or the content elements in the order expected by the editors. Extensions are expected to respect this
     * field.
     *
     * @param   string|null  $columnName
     *
     * @return $this
     * @see setSortable
     * @see isSortable
     */
    public function setSortByColumn(?string $columnName)
    {
        $this->config['ctrl']['sortby'] = $columnName;
        
        return $this;
    }
    
    /**
     * Returns true if the table is allowed on standard pages, and not only in folder items
     *
     * @return bool
     */
    public function isAllowedOnStandardPages(): bool
    {
        return (bool)$this->config['ctrl'][TablesOnStandardPagesStep::CONFIG_KEY];
    }
    
    /**
     * Use this if you want to allow this table to have records on standard pages and not only in folder items
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function setAllowOnStandardPages(bool $state = true)
    {
        $this->config['ctrl'][TablesOnStandardPagesStep::CONFIG_KEY] = $state;
        
        return $this;
    }
    
    /**
     * This allows you to set a class of the model which then will be mapped to this table
     *
     * @param   string  $className  The name of the model class to map to this table
     * @param   array   $columnMap  Optional list of fieldNames => propertyNames to be mapped
     *                              for extbase models
     *
     * @return $this
     * @deprecated will be removed in v11
     */
    public function addModelClass(string $className, array $columnMap = [])
    {
        return $this->registerModelClass($className, $columnMap);
    }
    
    /**
     * This allows you to set a class of the model which then will be mapped to this table
     *
     * @param   string  $className  The name of the model class to map to this table
     * @param   array   $columnMap  Optional list of fieldNames => propertyNames to be mapped
     *                              for extbase models
     *
     * @return $this
     */
    public function registerModelClass(string $className, array $columnMap = [])
    {
        $this->config['ctrl'][DomainModelMapStep::CONFIG_KEY][$className] = $columnMap;
        
        return $this;
    }
    
    /**
     * Allows you to remove a previously registered model class mapping
     *
     * @param   string  $className  The name of the model class to remove
     *
     * @return $this
     */
    public function removeModelClass(string $className)
    {
        unset($this->config['ctrl'][DomainModelMapStep::CONFIG_KEY][$className]);
        
        return $this;
    }
    
    /**
     * Returns the list of currently configured model classes for this table
     *
     * @return array
     */
    public function getModelClasses(): array
    {
        return array_unique($this->config['ctrl'][DomainModelMapStep::CONFIG_KEY] ?? []);
    }
    
    /**
     * Can be used to configure the order of tables when they are rendered in the "list" mode in the backend.
     * This table will be sorted either before or after the table with $otherTableName
     *
     * @param   string  $otherTableName  The table to relatively position this one to
     * @param   bool    $before          True by default, if set to false the table will be shown after the
     *                                   $otherTableName
     *
     * @return $this
     */
    public function setListPosition(string $otherTableName, bool $before = true)
    {
        $this->config['ctrl'][ListPositionStep::CONFIG_KEY][$before ? 'before' : 'after'][]
            = $this->getContext()->getRealTableName($otherTableName);
        
        return $this;
    }
    
    /**
     * If true: Records can be changed only by “admin”-users (having the “admin” flag set).
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#adminonly
     *
     * @return bool
     */
    public function isAdminOnly(): bool
    {
        return (bool)($this->config['ctrl']['adminOnly'] ?? false);
    }
    
    /**
     * If true: Records can be changed only by “admin”-users (having the “admin” flag set).
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#adminonly
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function setAdminOnly(bool $state = true)
    {
        $this->config['ctrl']['adminOnly'] = $state;
        
        return $this;
    }
    
    /**
     * Field name, which is automatically set to the current timestamp when the record is created. Is never modified
     * again. Typically the name “crdate” is used for that field. See tstamp example.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#crdate
     *
     * @return string
     */
    public function getCreatedAtColumn(): string
    {
        return $this->config['ctrl']['crdate'] ?? '';
    }
    
    /**
     * Field name, which is automatically set to the current timestamp when the record is created. Is never modified
     * again. Typically the name “crdate” is used for that field. See tstamp example.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#crdate
     *
     * @param   string|null  $columnName  The name of the database column to use
     *
     * @return $this
     */
    public function setCreatedAtColumn(?string $columnName)
    {
        $this->config['ctrl']['crdate'] = $columnName;
        
        return $this;
    }
    
    /**
     * Field name, which is automatically set to the uid of the backend user (be_users) who originally created the
     * record. Is never modified again. Typically the name “cruser_id” is used for that field. See tstamp example.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#cruser-id
     * @return string
     */
    public function getCreateUserColumn(): string
    {
        return $this->config['ctrl']['cruser_id'] ?? '';
    }
    
    /**
     * Field name, which is automatically set to the uid of the backend user (be_users) who originally created the
     * record. Is never modified again. Typically the name “cruser_id” is used for that field. See tstamp example.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#cruser-id
     *
     * @param   string|null  $columnName  The name of the database column to use
     *
     * @return $this
     */
    public function setCreateUserColumn(?string $columnName)
    {
        $this->config['ctrl']['cruser_id'] = $columnName;
        
        return $this;
    }
    
    
    /**
     * Field name, which indicates if a record is considered deleted or not.
     *
     * If this “soft delete” feature is used, then records are not really deleted, but just marked as ‘deleted’ by
     * setting the value of the field name to “1”. In turn, the whole system must strictly respect the record as
     * deleted. This means that any SQL query must exclude records where this field is true.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#delete
     *
     * @return string
     */
    public function getDeletedColumn(): string
    {
        return $this->config['ctrl']['delete'] ?? '';
    }
    
    /**
     * Field name, which indicates if a record is considered deleted or not.
     *
     * If this “soft delete” feature is used, then records are not really deleted, but just marked as ‘deleted’ by
     * setting the value of the field name to “1”. In turn, the whole system must strictly respect the record as
     * deleted. This means that any SQL query must exclude records where this field is true.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#delete
     *
     * @param   string|null  $columnName  The name of the database column to use
     *
     * @return $this
     */
    public function setDeletedColumn(?string $columnName)
    {
        $this->config['ctrl']['delete'] = $columnName;
        
        return $this;
    }
    
    /**
     * Field name where description of a record is stored in. This description is only displayed in the backend to
     * guide editors and admins and should never be shown in the frontend. If filled, the content of this field is
     * displayed in the page and list module, and shown above the field list if editing a record. It is meant as a note
     * field to give editors important additional information on single records. The TYPO3 core sets this property for
     * a series of main tables like be_users, be_groups and tt_content.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#descriptioncolumn
     *
     * @return string
     */
    public function getDescriptionColumn(): string
    {
        return $this->config['ctrl']['descriptionColumn'] ?? '';
    }
    
    /**
     * Field name where description of a record is stored in. This description is only displayed in the backend to
     * guide editors and admins and should never be shown in the frontend. If filled, the content of this field is
     * displayed in the page and list module, and shown above the field list if editing a record. It is meant as a note
     * field to give editors important additional information on single records. The TYPO3 core sets this property for
     * a series of main tables like be_users, be_groups and tt_content.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#descriptioncolumn
     *
     * @param   string|null  $columnName  The name of the database column to use
     *
     * @return $this
     */
    public function setDescriptionColumn(?string $columnName)
    {
        $this->config['ctrl']['descriptionColumn'] = $columnName;
        
        return $this;
    }
    
    
    /**
     * Field name, which – if set – will prevent all editing of the record for non-admin users.
     *
     * The field should be configured as a checkbox type. Non-admins could be allowed to edit the checkbox but if they
     * set it, they will effectively lock the record so they cannot edit it again – and they need an Admin-user to
     * remove the lock.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#editlock
     *
     * @return string
     */
    public function getEditLockColumn(): string
    {
        return $this->config['ctrl']['editlock'] ?? '';
    }
    
    /**
     * Field name, which – if set – will prevent all editing of the record for non-admin users.
     *
     * The field should be configured as a checkbox type. Non-admins could be allowed to edit the checkbox but if they
     * set it, they will effectively lock the record so they cannot edit it again – and they need an Admin-user to
     * remove the lock.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#editlock
     *
     * @param   string|null  $columnName  The name of the database column to use
     *
     * @return $this
     */
    public function setEditLockColumn(?string $columnName)
    {
        $this->config['ctrl']['editlock'] = $columnName;
        
        return $this;
    }
    
    /**
     * Is used to set a single, or multiple columns to sort the backend view with.
     * If the table is sortable or setSortColumn() is set, this is ignored.
     *
     * Can be either an array of column names ["name", "foo"] which results in the table being sorted ascending by both
     * columns. Alternatively you may define the direction by setting it as value of a key value pair ["name" => "asc",
     * "foo" => "desc"]
     *
     * @param   array|null  $columns
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#default-sortby
     */
    public function setBackendSortColumns(?array $columns)
    {
        if (is_array($columns)) {
            $list = [];
            foreach ($columns as $k => $v) {
                $column = is_numeric($k) ? $v : $k;
                $order = is_numeric($k) ? 'ASC' : strtoupper($v);
                $list[] = $column . ' ' . $order;
            }
            $columns = implode(', ', $list);
        }
        $this->config['ctrl']['default_sortby'] = $columns;
        
        return $this;
    }
    
    /**
     * Returns the list of configured backend order columns or null if there are none
     *
     * @return array|null
     */
    public function getBackendSortColumns(): ?array
    {
        $sortBy = $this->config['ctrl']['default_sortby'] ?? '';
        if (empty($sortBy)) {
            return null;
        }
        
        $columns = [];
        foreach (Arrays::makeFromStringList($sortBy) as $pair) {
            $pair = Arrays::makeFromStringList($pair, ' ');
            $key = array_shift($pair);
            $order = strtolower(array_shift($pair));
            if (! in_array($order, ['desc', 'asc'])) {
                $order = 'asc';
            }
            $columns[$key] = $order;
        }
        
        return $columns;
    }
    
    /**
     * Returns, if set the field name of the table which should be used as the “title” when the record is displayed in
     * the system.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#label
     * @return string
     */
    public function getLabelColumn(): string
    {
        return $this->config['ctrl']['label'] ?? '';
    }
    
    /**
     * Points to the field name of the table which should be used as the “title” when the record is displayed in the
     * system.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#label
     *
     * @param   string|null  $columnName
     *
     * @return $this
     */
    public function setLabelColumn(?string $columnName)
    {
        $this->config['ctrl']['label'] = $columnName;
        
        return $this;
    }
    
    /**
     * Returns a list of field names, which are holding alternative values to the value from the field pointed to
     * by “label” (see above) if that value is empty. May not be used consistently in the system, but should apply in
     * most cases.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#label-alt
     *
     * @return array
     */
    public function getLabelAlternativeColumns(): array
    {
        return Arrays::makeFromStringList($this->config['ctrl']['label_alt'] ?? '');
    }
    
    /**
     * Sets a list of field names, which are holding alternative values to the value from the field pointed to
     * by “label” (see above) if that value is empty. May not be used consistently in the system, but should apply in
     * most cases.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#label-alt
     *
     * @param   array|string  $columns  Either an array of field 1 of field names
     * @param   bool|NULL     $force    Optional: If set to true, the label_alt_force flag is set to true, which means
     *                                  the alternative labels will always be rendered
     *
     * @return $this
     */
    public function setLabelAlternativeColumns($columns, bool $force = null)
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        
        $this->config['ctrl']['label_alt'] = $columns;
        
        if (is_bool($force)) {
            $this->setForceLabelAlternative($force);
        }
        
        return $this;
    }
    
    /**
     * Returns the name of the column that is used to determine the "type" of the current table.
     * Note: This may contain a colon when the column of an external table should be used. See the documentation for
     * that!
     *
     * @return string
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#type
     */
    public function getTypeColumn(): string
    {
        return $this->config['ctrl']['type'] ?? '';
    }
    
    /**
     * Sets the name of the column that is used to determine the "type" of the current table.
     * The value of this field determines which one of the types configurations are used for displaying the fields in
     * the FormEngine. It will probably also affect how the record is used in the context where it belongs.
     *
     * The most widely known usage of this feature is the case of Content Elements where the “Type:” selector is
     * defined as the “type” field and when you change that selector you will also get another rendering of the form.
     *
     * $types and $typeFieldOptions allow you to not only configure the type columns name, but also create a column
     * entry for it on the fly
     *
     * @param   string|null  $columnName        The name of the database column
     * @param   array|null   $types             A list of types as $id => $label array. This follows the same logic as
     *                                          a select preset:
     *                                          {@link \LaborDigital\T3ba\FormEngine\FieldPreset\Basics::applySelect()}
     * @param   array|null   $typeFieldOptions  Allows to provide additional options for the generated field. See
     *                                          the link above to learn more about the possible options
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#type
     */
    public function setTypeColumn(?string $columnName, ?array $types = [], ?array $typeFieldOptions = [])
    {
        $this->config['ctrl']['type'] = $columnName;
        
        // Apply the type definition as a field automatically if it was provided
        if ($columnName && is_array($types)) {
            $typeFieldOptions = $typeFieldOptions ?? [];
            $typeFieldOptions['maxItems'] = 1;
            $typeFieldOptions['default'] = $typeFieldOptions['default'] ?? key($types);
            
            // Add the field to the default type
            $type = $this->getType();
            $tabKeys = iterator_to_array($type->getTabKeys(), false);
            $firstTab = reset($tabKeys);
            $type->getField($columnName)
                 ->setReloadOnChange()
                 ->applyPreset()
                 ->select($types, $typeFieldOptions)
                 ->moveTo('top:' . $firstTab);
        }
        
        return $this;
    }
    
    /**
     * Returns true if the label_alt_force marker is set.
     * If set, then the label_alt fields are always shown in the title separated by comma.
     *
     * @return bool
     */
    public function isLabelAlternativeForced(): bool
    {
        return (bool)($this->config['ctrl']['label_alt_force'] ?? false);
    }
    
    /**
     * If set, then the label_alt fields are always shown in the title separated by comma.
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function setForceLabelAlternative(bool $state = true)
    {
        $this->config['ctrl']['label_alt_force'] = $state;
        
        return $this;
    }
    
    /**
     * Contains the system name of the table. Is used for display in the backend.
     *
     * For instance the “tt_content” table is of course named “tt_content” technically. However in the backend display
     * it will be shown as “Page Content” when the backend language is English. When another language is chosen, like
     * Danish, then the label “Sideindhold” is shown instead. This value is managed by the “title” value.
     *
     * You can insert plain text values, but the preferred way is to enter a reference to a localized string. Refer to
     * the Localization section for more details.
     *
     * The default value is the humanized table name
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#title
     *
     * @return string
     */
    public function getTitle(): string
    {
        $title = $this->config['ctrl']['title'] ?? '';
        
        if (empty($title)) {
            return Inflector::toHuman(preg_replace('/^(.*?_domain_model_)/', '', $this->getTableName()));
        }
        
        return $title;
    }
    
    /**
     * Contains the system name of the table. Is used for display in the backend.
     *
     * For instance the “tt_content” table is of course named “tt_content” technically. However in the backend display
     * it will be shown as “Page Content” when the backend language is English. When another language is chosen, like
     * Danish, then the label “Sideindhold” is shown instead. This value is managed by the “title” value.
     *
     * You can insert plain text values, but the preferred way is to enter a reference to a localized string. Refer to
     * the Localization section for more details.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#title
     *
     * @param   string  $title
     *
     * @return $this
     */
    public function setTitle(string $title)
    {
        // Make sure we supply a real translation key for the table title
        // Because typo3 can't do some stuff if you don't use translation keys for a title...
        // What it can't do? Well, map the table to a extension for example /o\
        $title = $this->getContext()->cs()->translator->getLabelKey($title);
        $this->config['ctrl']['title'] = $title;
        
        return $this;
    }
    
    /**
     * Sets the list of fields from the table that will be included when searching for records in the TYPO3
     * backend. No record from a table will ever be found if that table does not have “searchFields” defined.
     *
     * @param   array  $columns
     *
     * @return $this
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#searchfields
     */
    public function setSearchColumns(array $columns)
    {
        $searchFields = array_unique($columns);
        $this->config['ctrl']['searchFields'] = implode(',', $searchFields);
        
        return $this;
    }
    
    /**
     * Adds some fields to the list of fields from the table that will be included when searching for records in the
     * TYPO3 backend. The existing fields will be kept.
     *
     * @param   array  $columns
     *
     * @return $this
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#searchfields
     */
    public function addSearchColumns(array $columns)
    {
        return $this->setSearchColumns(array_merge($this->getSearchColumns(), $columns));
    }
    
    /**
     * Returns the currently configured the of fields from the table that will be included when searching for records
     * in the TYPO3 backend.
     *
     * @return array
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#searchfields
     */
    public function getSearchColumns(): array
    {
        return Arrays::makeFromStringList($this->config['ctrl']['searchFields'] ?? '');
    }
    
    /**
     * Sets the icon that is displayed for this table
     * Pointing to the icon file to use for the table. Icons should be square SVGs. In case you cannot supply a SVG you
     * can still use a PNG file of 64x64 pixels in dimension.
     *
     * @param   string  $filename  Either an absolute filename, or one of the following options:
     *                             EXT:{{extKey}}/Resources/Public/Icons/icon.svg
     *                             ./Resources/Public/Icons/icon.svg <- for the current extension
     *
     * @return $this
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#iconfile
     */
    public function setIconFile(string $filename)
    {
        if (str_starts_with($filename, './')) {
            $filename = 'EXT:{{extKey}}' . substr($filename, 1);
        }
        
        $this->config['ctrl']['iconfile']
            = $this->getContext()->getExtConfigContext()->replaceMarkers($filename);
        
        return $this;
    }
    
    /**
     * Returns the currently set path to the icon file or null.
     *
     * @return string|null
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#iconfile
     */
    public function getIconFile(): ?string
    {
        return $this->config['ctrl']['iconfile'] ?? null;
    }
    
    /**
     * Registers a new context sensitive help file for this TCA table.
     *
     * @param   string  $filename   The full filename which should begin with EXT:ext_key/....
     *                              If there is no path but only a filename is given, the default path will
     *                              automatically be prepended. So: locallang_custom.xlf becomes
     *                              EXT:{{extKey}}/Resources/Private/Language/locallang_custom.xlf
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/ContextSensitiveHelp/Index.html
     */
    public function registerCSHFile(string $filename): self
    {
        if (basename($filename) === $filename) {
            $filename = 'EXT:{{extKey}}/Resources/Private/Language/' . $filename;
        }
        
        $this->config['ctrl'][CshLabelStep::CONFIG_KEY][md5($filename)]
            = $this->getContext()->getExtConfigContext()->replaceMarkers($filename);
        
        return $this;
    }
    
    /**
     * Returns the context sensitive help files that have been registered for this TCA table.
     *
     * WARNING: This only includes the files that were registered through the table classes!
     *
     * @return array
     */
    public function getCSHFiles(): array
    {
        return array_values($this->config['ctrl'][CshLabelStep::CONFIG_KEY] ?? []);
    }
}
