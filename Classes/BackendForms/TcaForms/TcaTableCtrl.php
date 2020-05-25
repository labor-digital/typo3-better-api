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
 * Last modified: 2020.03.19 at 03:04
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\TcaForms;

use LaborDigital\Typo3BetterApi\Translation\TranslationService;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

class TcaTableCtrl
{
    
    /**
     * @var array
     */
    protected $ctrl;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
     */
    protected $table;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    protected $translationService;
    
    /**
     * TcaTableCtrl constructor.
     *
     * @param array                                                       $ctrl
     * @param \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable $table
     * @param \LaborDigital\Typo3BetterApi\Translation\TranslationService $translationService
     */
    public function __construct(array $ctrl, TcaTable $table, TranslationService $translationService)
    {
        $this->ctrl = $ctrl;
        $this->table = $table;
        $this->translationService = $translationService;
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
        return Arrays::getPath($this->ctrl, 'adminOnly', false) === true;
    }
    
    /**
     * If true: Records can be changed only by “admin”-users (having the “admin” flag set).
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#adminonly
     *
     * @param bool $state
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setAdminOnly(bool $state): TcaTableCtrl
    {
        $this->ctrl['adminOnly'] = $state;
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
        return Arrays::getPath($this->ctrl, 'crdate', '');
    }
    
    /**
     * Field name, which is automatically set to the current timestamp when the record is created. Is never modified
     * again. Typically the name “crdate” is used for that field. See tstamp example.
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#crdate
     *
     * @param string $column The name of the database column to use
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setCreatedAtColumn(string $column): TcaTableCtrl
    {
        $this->ctrl['crdate'] = $column;
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
        return Arrays::getPath($this->ctrl, 'cruser_id', '');
    }
    
    /**
     * Field name, which is automatically set to the uid of the backend user (be_users) who originally created the
     * record. Is never modified again. Typically the name “cruser_id” is used for that field. See tstamp example.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#cruser-id
     *
     * @param string $column The name of the database column to use
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setCreateUserColumn(string $column): TcaTableCtrl
    {
        $this->ctrl['cruser_id'] = $column;
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
        return Arrays::getPath($this->ctrl, 'delete', '');
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
     * @param string $column The name of the database column to use
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setDeletedColumn(string $column): TcaTableCtrl
    {
        $this->ctrl['delete'] = $column;
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
        return Arrays::getPath($this->ctrl, 'descriptionColumn', '');
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
     * @param string $column The name of the database column to use
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setDescriptionColumn(string $column): TcaTableCtrl
    {
        $this->ctrl['descriptionColumn'] = $column;
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
        return Arrays::getPath($this->ctrl, 'editlock', '');
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
     * @param string $column The name of the database column to use
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setEditLockColumn(?string $column): TcaTableCtrl
    {
        $this->ctrl['editlock'] = $column;
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
     * @param array|null $columns
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#default-sortby
     */
    public function setBackendSortColumns(?array $columns): TcaTableCtrl
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
        $this->ctrl['default_sortby'] = $columns;
        return $this;
    }
    
    /**
     * Returns the list of configured backend order columns or null if there are none
     *
     * @return array|null
     */
    public function getBackendSortColumns(): ?array
    {
        if (!is_string($this->ctrl['default_sortby'])) {
            return null;
        }
        $columns = [];
        foreach (Arrays::makeFromStringList($this->ctrl['default_sortby']) as $pair) {
            $pair = Arrays::makeFromStringList($pair, ' ');
            $key = array_shift($pair);
            $order = strtolower(array_shift($pair));
            if (!in_array($order, ['desc', 'asc'])) {
                $order = 'asc';
            }
            $columns[$key] = $order;
        }
        return $columns;
    }
    
    /**
     * Field name, which is used to manage the order of the records when displayed.
     *
     * NOTE: If you use applyPresets(["sortable"]) This field is automatically configured
     *
     * The field contains an integer value which positions it at the correct position between other records from the
     * same table on the current page.
     *
     * This feature is used by e.g. the “pages” table and “tt_content” table (Content Elements) in order to output the
     * pages or the content elements in the order expected by the editors. Extensions are expected to respect this
     * field.
     *
     * @param string|null $column
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#sortby
     */
    public function setSortColumn(?string $column): TcaTableCtrl
    {
        $this->ctrl['sortby'] = $column;
        return $this;
    }
    
    /**
     * Returns the field name, which is used to manage the order of the records when displayed.
     *
     * @return string|null
     */
    public function getSortColumn(): ?string
    {
        return $this->ctrl['sortby'];
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
        return Arrays::getPath($this->ctrl, 'label', '');
    }
    
    /**
     * Points to the field name of the table which should be used as the “title” when the record is displayed in the
     * system.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#label
     *
     * @param string $column
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setLabelColumn(?string $column): TcaTableCtrl
    {
        $this->ctrl['label'] = $column;
        return $this;
    }
    
    /**
     * Returns a list of field names, which are holding alternative values to the value from the field pointed to
     * by “label” (see above) if that value is empty. May not be used consistently in the system, but should apply in
     * most cases.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#label-alt
     *
     * @return string
     */
    public function getLabelAlternativeColumns(): string
    {
        return Arrays::getPath($this->ctrl, 'label_alt', '');
    }
    
    /**
     * Sets a list of field names, which are holding alternative values to the value from the field pointed to
     * by “label” (see above) if that value is empty. May not be used consistently in the system, but should apply in
     * most cases.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#label-alt
     *
     * @param array|string $columns Either an array of field 1 of field names
     * @param bool|NULL    $force   Optional: If set to true, the label_alt_force flag is set to true, which means the
     *                              alternative labels will always be rendered
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setLabelAlternativeColumns($columns, bool $force = null): TcaTableCtrl
    {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        $this->ctrl['label_alt'] = $columns;
        if (is_bool($force)) {
            $this->setForceLabelAlternative($force);
        }
        return $this;
    }
    
    /**
     * Returns the name of the column that is used to determine the "type" of the current table.
     * Note: This may contain a colon when the column of an external table should be used. See the documentation for
     * that!
     * @return string
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#type
     */
    public function getTypeColumn(): string
    {
        return Arrays::getPath($this->ctrl, 'type', '');
    }
    
    /**
     * Sets the name of the column that is used to determine the "type" of the current table.
     * The value of this field determines which one of the types configurations are used for displaying the fields in
     * the FormEngine. It will probably also affect how the record is used in the context where it belongs.
     *
     * The most widely known usage of this feature is the case of Content Elements where the “Type:” selector is
     * defined as the “type” field and when you change that selector you will also get another rendering of the form:
     *
     * @param string|null $typeColumn
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#type
     */
    public function setTypeColumn(?string $typeColumn): TcaTableCtrl
    {
        $this->ctrl['type'] = $typeColumn;
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
        return (bool)Arrays::getPath($this->ctrl, 'label_alt_force', false);
    }
    
    /**
     * If set, then the label_alt fields are always shown in the title separated by comma.
     *
     * @param bool $state
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setForceLabelAlternative(bool $state): TcaTableCtrl
    {
        $this->ctrl['label_alt_force'] = $state;
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
        $title = Arrays::getPath($this->ctrl, 'title', '');
        if (empty($title)) {
            return Inflector::toHuman(preg_replace('/^(.*?_domain_model_)/', '', $this->table->getTableName()));
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
     * @param string $title
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     */
    public function setTitle(string $title): TcaTableCtrl
    {
        $this->ctrl['title'] = $title;
        return $this;
    }
    
    /**
     * Sets the list of fields from the table that will be included when searching for records in the TYPO3
     * backend. No record from a table will ever be found if that table does not have “searchFields” defined.
     *
     * @param array $columns
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#searchfields
     */
    public function setSearchColumns(array $columns): TcaTableCtrl
    {
        $searchFields = array_unique($columns);
        $this->ctrl['searchFields'] = implode(',', $searchFields);
        return $this;
    }
    
    /**
     * Adds some fields to the list of fields from the table that will be included when searching for records in the
     * TYPO3 backend. The existing fields will be kept.
     *
     * @param array $columns
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#searchfields
     */
    public function addSearchColumns(array $columns): TcaTableCtrl
    {
        return $this->setSearchColumns(array_merge($this->getSearchColumns(), $columns));
    }
    
    /**
     * Returns the currently configured the of fields from the table that will be included when searching for records
     * in the TYPO3 backend.
     * @return array
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#searchfields
     */
    public function getSearchColumns(): array
    {
        $fields = Arrays::getPath($this->ctrl, 'searchFields', '');
        return Arrays::makeFromStringList($fields);
    }
    
    /**
     * Sets the icon that is displayed for this table
     * Pointing to the icon file to use for the table. Icons should be square SVGs. In case you cannot supply a SVG you
     * can still use a PNG file of 64x64 pixels in dimension.
     *
     * @param string $filename
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#iconfile
     */
    public function setIconFile(string $filename): TcaTableCtrl
    {
        $this->ctrl['iconfile'] = $filename;
        return $this;
    }
    
    /**
     * Returns the currently set path to the icon file or null.
     * @return string|null
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html#iconfile
     */
    public function getIconFile(): ?string
    {
        return Arrays::getPath($this->ctrl, 'iconfile');
    }
    
    /**
     * Can be used to set raw config values, that are not implemented in this facade.
     * Set either key => value pairs, or an Array of key => value pairs
     *
     * @param array|string|int $key   Either a key to set the given $value for, or an array of $key => $value pairs
     * @param null             $value The value to set for the given $key (if $key is not an array)
     *
     * @return $this
     */
    public function setRaw($key, $value = null)
    {
        if (is_array($key)) {
            $this->ctrl = $key;
        } else {
            $this->ctrl[$key] = $value;
        }
        return $this;
    }
    
    /**
     * Returns the raw configuration array for this object
     * @return array
     */
    public function getRaw(): array
    {
        $ctrl = $this->ctrl;
        
        // Make sure we supply a real translation key for the table title
        // Because typo3 can't do some stuff if you don't use translation keys for a title...
        // What it can't do? Well, map the table to a extension for example /o\
        if (isset($ctrl['title'])) {
            $ctrl['title'] = $this->translationService->getTranslationKeyMaybe($ctrl['title']);
        } else {
            $ctrl['title'] = $this->getTitle();
        }
        
        // Done
        return $ctrl;
    }
}
