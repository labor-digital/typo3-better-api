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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\FormEngine\FieldPreset;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\IntegerType;
use LaborDigital\T3ba\FormEngine\Wizard\FileLinkPreviewWizard;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\BasePidOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\DefaultOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\ElementBrowserFilterOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\EvalOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\FileBaseDirOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\FileExtListOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\FileGenericOverrideChildTcaOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\FileImageCropOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\FileImageThumbnailSizeOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\InheritParentDefinitionOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\LegacyBasePidToLimitToPidsRewriteOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\LegacyReadOnlyOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\LimitToPidsOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MinMaxItemOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MmTableOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\SelectItemsOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\SelectSideBySideOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\UserFuncOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\WizardAllowEditOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\WizardAllowNewOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Category\CategoryRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class Relations extends AbstractFieldPreset
{
    
    /**
     * Converts your field into a category field. That's it actually...
     * For further details on what categories are and how they work take a look at:
     *
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Categories/Index.html
     *
     * @param   array  $options  Additional options for this preset
     *                           - minItems int (0): The minimum number of items required to be valid
     *                           - maxItems int: The maximum number of items allowed in this field
     *                           - required bool (FALSE): If set to true, the field requires at least 1 item.
     *                           This is identical with setting minItems to 1
     *                           - sideBySide bool (FALSE): If set to true the categories are shown as two columns
     *                           instead of the tree view
     *                           - limitToPids int|string|bool|array (TRUE): Can be used to limit the item selection
     *                           to a certain pid, or a list of pids. True for the current pid, false for no limiting, a numeric pid, a pid selector
     *                           or an array containing either numeric or string values.
     *                           - limitToPidsRecursive bool (FALSE): If set to true, the items will be resolved RECURSIVELY
     *                           based on the provided "limitToPids" option
     *                           - limitToPidsRecursiveDepth int (10): If "limitToPidsRecursive" is true, this option defines
     *                           how many levels of recursion we should resolve
     *                           --- TRUE: Setting this to true will only show categories on the same pid than the
     *                           record
     *                           --- FALSE: Setting this to false will disable the pid constrain and show all
     *                           categories
     *                           --- number: Sets the pid constraint to that specific page
     *                           --- (at)pid...: Sets the pid constraint to the page registered with that selector
     *                           --- array: Sets the pid constraint to any of the pids in the list
     */
    public function applyCategorize(array $options = []): void
    {
        $o = $this->initializeOptions([
            new SelectSideBySideOption(),
            new LimitToPidsOption('sys_category'),
            new MinMaxItemOption(),
            new EvalOption(['required']),
        ]);
        
        $this->context->configureSqlColumn(
            static function (Column $column) {
                $column->setType(new IntegerType())
                       ->setLength(11)
                       ->setDefault(0);
            }
        );
        
        $o->validate($options);
        $this->field->addConfig(
            $o->apply(
                CategoryRegistry::getTcaFieldConfiguration(
                    $this->context->getTcaTable()->getTableName(),
                    $this->field->getId(),
                    ['size' => 7]
                )
            )
        );
    }
    
    /**
     * This converts your field into a fully fledged relation field. You should use this method if you want your user
     * to choose records from anywhere on your page using a selector popup. This type also allows the relation to
     * multiple, different types of other records to relate to.
     *
     * @param   string|array  $foreignTable  Either a single table, or an array of tables to relate to.
     *                                       Hint: using ...table will automatically unfold your table to
     *                                       tx_yourext_domain_model_table
     * @param   array         $options       Additional options for the relation
     *                                       - readOnly bool (FALSE): True to make this field read only
     *                                       - minItems int (0): The minimum number of items required to be valid
     *                                       - maxItems int: The maximum number of items allowed in this field
     *                                       - required bool (FALSE): If set to true, the field requires at least 1
     *                                       item. This is identical with setting minItems to 1
     *                                       - basePid int|string|array|true: Can be set to preset the "select window" to a
     *                                       certain page id. Highly convenient for the editor.
     *                                       If you define multiple tables in $foreignTable, you can also provide
     *                                       an array of table => pid mappings for all of them. If a
     *                                       table is not in your mapping, it will be opened normally.
     *                                       Table-shorthands are supported, as well as pid string identifiers.
     *                                       If TRUE is provided, the CURRENT pid will be used as constraint.
     *                                       - allowNew bool (FALSE): If set new records can be created with the new
     *                                       record wizard
     *                                       - allowEdit bool (TRUE): Can be used to disable the editing of records in
     *                                       the current group
     *                                       - filters array: A list of filter functions to apply for this group.
     *                                       The filter should be supplied like a typical TYPO3 callback
     *                                       class->function. If the filter is given as array, the first value will be
     *                                       used as callback and the second as parameters. Note: This feature is not
     *                                       implemented in the element browser for Flex forms in the TYPO3 core... The
     *                                       filtering of the element browser only works for TCA fields!
     *                                       - mmTable bool (AUTO): By default the script will automatically create
     *                                       an mm table for this field if it is required. If your field defines
     *                                       maxItems = 1 there is no requirement for an mm table so we will just
     *                                       use a 1:1 relation in the database. If you, however want this field
     *                                       to always use an mmTable, just set this to TRUE manually
     *                                       - mmOpposite string: Allows you to create a link between this field
     *                                       and the field of the related table. This defines the name of the
     *                                       field on the $foreignTable. NOTE: This only works if a single $foreignTable
     *                                       exists! Additionally, you need to create the field on the foreign table
     *                                       manually. I would suggest using the "relationGroupOpposite" preset to do so.
     *                                       {@see https://docs.typo3.org/m/typo3/reference-tca/10.4/en-us/ColumnsConfig/Type/Group.html#mm-opposite-field}
     *
     *                                       LEGACY SUPPORT
     *                                       - mmTableName string: When given this table name is set as mm table name
     *                                       instead of the automatically generated one. Useful for legacy codebase.
     *
     *                                       DEPRECATED: Will be removed in v12
     *                                       - readOnly bool (FALSE): True to make this field read only
     *                                       use the setReadOnly() method on a field instead
     */
    public function applyRelationGroup($foreignTable, array $options = []): void
    {
        $tables = $this->context->getRealTableNameList($foreignTable);
        
        $o = $this->initializeOptions([
            new ElementBrowserFilterOption(),
            new LegacyReadOnlyOption(),
            new MmTableOption($tables),
            new MinMaxItemOption(),
            new BasePidOption(true),
            new EvalOption(['required']),
            new WizardAllowNewOption(),
            new WizardAllowEditOption(),
        ]);
        
        $options = $o->validate($options);
        
        $this->field->addConfig(
            $o->apply([
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => implode(',', $tables),
                'foreign_table' => count($tables) === 1 ? reset($tables) : null,
                'size' => $options['maxItems'] === 1 ? 1 : 3,
                'multiple' => 0,
                'localizeReferencesAtParentLocalization' => true,
            ])
        );
    }
    
    /**
     * This preset can be used to create the counterpart of a field that defines the "mmOpposite" option.
     * It creates a field in the child record that allows you to modify or (if readOnly is enabled) to display
     * related records.
     *
     * @param   string|array  $foreignTable  The parent table, that has the group field for which this field is the opposite
     *                                       Hint: using ...table will automatically unfold your table to
     *                                       tx_yourext_domain_model_table
     * @param   string        $foreignField  The name of the field in the parent table, for which this field is the opposite.
     * @param   array         $options       Additional options for creating the relation
     *                                       - mmTableName string: When given this table name is set as mm table name
     *                                       instead of the automatically generated one. Useful for legacy codebase.
     *
     *                                       DEPRECATED: Will be removed in v12
     *                                       - readOnly bool (FALSE): True to make this field read only
     *                                       use the setReadOnly() method on a field instead
     *
     * @see \LaborDigital\T3ba\FormEngine\FieldPreset\Relations::applyRelationGroup() mostly the "mmOpposite" option
     * @see https://docs.typo3.org/m/typo3/reference-tca/10.4/en-us/ColumnsConfig/Type/Group.html#mm-opposite-field
     */
    public function applyRelationGroupOpposite($foreignTable, string $foreignField, array $options = []): void
    {
        $o = $this->initializeOptions([
            new MmTableOption(),
            new LegacyReadOnlyOption(),
        ]);
        
        $foreignTableName = $this->context->getRealTableName($foreignTable);
        
        $options = $o->validate($options);
        
        if (empty($options['mmTableName'])) {
            $options['mmTableName'] = $this->context->cs()->sqlRegistry->makeMmTableName(
                $foreignTableName,
                $this->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::TCA_V11_MM_TABLES)
                    ? null : $foreignField
            );
        }
        
        $this->applyRelationGroup($foreignTableName, [
            'readOnly' => $options['readOnly'],
            'mmTable' => false,
        ]);
        
        $this->field->addConfig([
            'allowed' => $foreignTableName,
            'MM' => $options['mmTableName'],
            'foreign_table' => '__UNSET',
            'MM_match_fields' => [
                'tablenames' => $foreignTableName,
                'fieldname' => $foreignField,
            ],
        ]);
        
        $this->context->configureSqlColumn(static function (Column $column) {
            $column->setType(new IntegerType())
                   ->setLength(11)
                   ->setNotnull(true)
                   ->setDefault(0);
        });
    }
    
    /**
     * This sets your field to be a relation to a single, or multiple pages.
     * By default only a single page can be set, but you may use maxItems to create relations to multiple pages
     *
     * @param   array  $options  Additional options for the relation
     *                           - minItems int (0): The minimum number of items required to be valid
     *                           - maxItems int: The maximum number of items allowed in this field
     *                           - required bool (FALSE): If set to true, the field requires at least 1 item.
     *                           This is identical with setting minItems to 1
     */
    public function applyRelationPage(array $options = []): void
    {
        if (! isset($options['maxItems'])) {
            $options['maxItems'] = 1;
        }
        
        if (! isset($options['allowEdit'])) {
            $options['allowEdit'] = false;
        }
        
        $this->applyRelationGroup('pages', $options);
    }
    
    /**
     * This sets your field to be a relation to one or multiple files in TYPO3's FAL storage.
     *
     * @param   array  $options  Additional options for the relation
     *                           - minItems int (0): The minimum number of items required to be valid
     *                           - maxItems int: The maximum number of items allowed in this field
     *                           - required bool (FALSE): If set to true, the field requires at least 1 item.
     *                           This is identical with setting minItems to 1
     *                           - allowList string: A comma separated list of file extensions that are specifically
     *                           ALLOWED to be uploaded, every other extension will be blocked
     *                           - baseDir string: Either a fully qualified fal identifier like 1:/folder-name/ or just
     *                           a simple folder name like folder-name that is always used/selected by default
     *                           if the object browser is opened
     *                           - disableFalFields array|true: An optional list of sys_file_reference fields
     *                           that should be disabled for this field. This allows you to remove some input
     *                           options on the fly. TRUE disables ALL fields.
     *                           This has no effect if the field is inside a flex form section!
     *
     *                           DEPRECATED:
     *                           - blockList string: A comma separated list of file extensions that are specifically
     *                           BLOCKED to be uploaded, every other extension will be allowed
     *                           NOTE: This feature does not really work as it is intended. This is a filter
     *                           that ONLY works in the elementBrowser and is therefore completely useless
     *                           This option will be removed in v12
     */
    public function applyRelationFile(array $options = []): void
    {
        $o = $this->initializeOptions([
            new FileExtListOption(),
            new FileBaseDirOption(),
            new FileGenericOverrideChildTcaOption(),
            new EvalOption(['required']),
            new MinMaxItemOption(),
        ]);
        $options = $o->validate($options);
        
        if ($this->context->isInFlexFormSection()) {
            // Inside a section
            $this->field->applyPreset()->customWizard(FileLinkPreviewWizard::class);
            $config = [
                'type' => 'input',
                'softref' => 'typolink',
                'renderType' => 'inputLink',
                'fieldControl' => [
                    'linkPopup' => [
                        'options' => [
                            'allowedExtensions' => $options['allowList'],
                            'blindLinkOptions' => 'page,url,telephone,folder,mail',
                            'blindLinkFields' => 'class,params,target,title',
                        ],
                    ],
                ],
            ];
        } else {
            // Default field
            $r = ExtensionManagementUtility::getFileFieldTCAConfig(
                $this->field->getId(),
                [],
                $options['allowList'],
                $options['blockList']
            );
            
            $config = Arrays::merge($r, [
                'foreign_match_fields' => [
                    'tablenames' => $this->context->getTcaTable()->getTableName(),
                    'table_local' => 'sys_file',
                ],
                'appearance' => [
                    'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference',
                    'fileUploadAllowed' => false,
                    'fileByUrlAllowed' => false,
                    'showSynchronizationLink' => true,
                    'showAllLocalizationLink' => true,
                    'showPossibleLocalizationRecords' => true,
                ],
            ]);
            
            $this->context->configureSqlColumn(static function (Column $column) {
                $column->setType(new IntegerType())
                       ->setLength(11)
                       ->setDefault(0);
            });
        }
        
        $this->field->addConfig(['overrideChildTca' => '__UNSET']);
        $this->field->addConfig($o->apply($config));
    }
    
    /**
     * Similar to relationFile but is already preconfigured to show online media, like youtube or vimeo videos.
     *
     * @param   array  $options  Additional options for the relation
     *                           - allowYoutube bool (TRUE): Allow youtube videos
     *                           - allowVimeo bool (FALSE): Allow vimeo videos
     *                           - minItems int (0): The minimum number of items required to be valid
     *                           - maxItems int: The maximum number of items allowed in this field
     *                           - required bool (FALSE): If set to true, the field requires at least 1 item.
     *                           This is identical with setting minItems to 1
     *                           - baseDir string: Either a fully qualified fal identifier like 1:/folder-name/ or just
     *                           a simple folder name like folder-name that is always used/selected by default
     *                           if the object browser is opened
     */
    public function applyRelationOnlineMedia(array $options = []): void
    {
        $options = Options::make($options, [
            'allowYoutube' => [
                'type' => 'bool',
                'default' => true,
            ],
            'allowVimeo' => [
                'type' => 'bool',
                'default' => false,
            ],
        ], ['allowUnknown' => true]);
        
        // Build the allow-list
        $allowList = implode(',', array_filter([
            $options['allowYoutube'] ? 'youtube' : '',
            $options['allowVimeo'] ? 'vimeo' : '',
        ]));
        $options['allowList'] = $allowList;
        unset($options['allowYoutube'], $options['allowVimeo']);
        
        $this->applyRelationFile($options);
    }
    
    /**
     * Similar to relationFile but is already preconfigured to allow only image files
     *
     * @param   array  $options  Additional options for the relation
     *                           - minItems int (0): The minimum number of items required to be valid
     *                           - maxItems int: The maximum number of items allowed in this field
     *                           - required bool (FALSE): If set to true, the field requires at least 1 item.
     *                           This is identical with setting minItems to 1
     *                           - allowList string (same as: GFX.imagefile_ext): A comma separated list of file
     *                           extensions that are specifically ALLOWED to be uploaded, every other extension
     *                           will be blocked
     *                           - blockList string: A comma separated list of file extensions that are specifically
     *                           BLOCKED to be uploaded, every other extension will be allowed
     *                           - allowCrop bool (TRUE): By default all images are allowed to be cropped using the
     *                           "crop" button. If you don't want that feature set this to FALSE
     *                           - useDefaultCropVariant bool (TRUE): Set this to FALSE to disable the default crop
     *                           variant
     *                           - cropVariants array: A list of different crop variants you want to use for this image
     *                           You define a crop variant by using an array like the definition, seen below.
     *                           By default the aspect ratio for cropping images is free, but as you see, you can use
     *                           "aspectRatios" to provide additional aspect ratios that will be available for
     *                           this image. Define the ratios in an array where key is the aspect ratio like "1:1" and
     *                           the value is the label that describes that aspect ratio for the backend editor
     *                           You can use the special "free" or "NaN" keys to provide an additional "free" mode
     *                           - baseDir string: Either a fully qualified fal identifier like 1:/folder-name/ or just
     *                           a simple folder name like folder-name that is always used/selected by default
     *                           if the object browser is opened
     *                           - disableFalFields array: An optional list of sys_file_reference fields
     *                           that should be disabled for this field. This allows you to remove some input
     *                           options on the fly
     *                           - thumbnailSize array ([200,150]): An array containing the width(0)
     *                           and height(1) of the image thumbnail
     *
     * CropVariantsArray:
     *  "cropVariants" => [
     *    [
     *        "title" => "YOUR_TRANSLATABLE_LABEL",
     *        "aspectRatios" => [
     *            "19:9" => "YOUR_TRANSLATABLE_LABEL",
     *            "1:1" => "YOUR_TRANSLATABLE_LABEL",
     *            "free" => "YOUR_TRANSLATABLE_LABEL"
     *        ]
     *        ... Additional, default TCA Options
     *    ]
     *  ]
     *
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/ImageManipulation.html
     */
    public function applyRelationImage(array $options = []): void
    {
        $o = $this->initializeOptions([
            new FileExtListOption($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']),
            new FileImageCropOption(),
            new FileImageThumbnailSizeOption(),
            new InheritParentDefinitionOption(),
        ]);
        
        $options = $o->validate($options, ['allowUnknown' => true]);
        
        $this->applyRelationFile($options);
        
        $this->field->addConfig($o->apply());
    }
    
    /**
     * This converts your field into a fully fledged relation field using Typo3's "select" type.
     * You should use this method if you want your user to select a number of objects out of a predefined selection of
     * records. The field will automatically switch between a select dropdown and a "selectMultiple" interface
     * based on your configuration of "maxItems". maxItems = 1 means a select box, maxItems > 1 means the select
     * multiple interface
     *
     * @param   string  $foreignTable   The foreign table to create the relations to
     *                                  Hint: using ...table will automatically unfold your table to
     *                                  tx_yourext_domain_model_table
     * @param   array   $options        Additional options for the relation
     *                                  - minItems int (0): The minimum number of items required to be valid
     *                                  - maxItems int: The maximum number of items allowed in this field
     *                                  - required bool (FALSE): If set to true, the field requires at least 1 item.
     *                                  This is identical with setting minItems to 1
     *                                  - where string: Can be used to limit the selection of records from the foreign
     *                                  table. This should be a SQL conform string that starts with an "AND ..." see
     *                                  also: https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Select.html#id93
     *                                  - limitToPids int|string|bool|array (FALSE): Can be used to limit the item selection
     *                                  to a certain pid, or a list of pids. True for the current pid, false for no limiting, a numeric pid, a pid selector
     *                                  or an array containing ither numeric or string values.
     *                                  - limitToPidsRecursive bool (FALSE): If set to true, the items will be resolved RECURSIVELY
     *                                  based on the provided "limitToPids" option
     *                                  - limitToPidsRecursiveDepth int (10): If "limitToPidsRecursive" is true, this option defines
     *                                  how many levels of recursion we should resolve
     *                                  - userFunc string: Can be given like any select itemProcFunc in typo3 as:
     *                                  vendor\className->methodName and is used as a filter for the items in the select
     *                                  field
     *                                  - mmTable bool (AUTO): By default the script will automatically create
     *                                  an mm table for this field if it is required. If your field defines
     *                                  maxItems = 1 there is no requirement for an mm table so we will just
     *                                  use a 1:1 relation in the database. If you, however want this field
     *                                  to always use an mmTable, just set this to TRUE manually
     *                                  - mmOpposite string: Allows you to create a link between this field
     *                                  and the field of the related table. This defines the name of the
     *                                  field on the $foreignTable. NOTE: This only works if a single $foreignTable
     *                                  exists! Additionally, you need to create the field on the foreign table
     *                                  manually. I would suggest using the "relationGroupOpposite" preset to do so.
     *                                  {@see https://docs.typo3.org/m/typo3/reference-tca/10.4/en-us/ColumnsConfig/Type/Group.html#mm-opposite-field}
     *                                  - additionalItems array: Additional items that should be attached to the
     *                                  list of items gathered by the relation lookup. Provide an array of $key =>
     *                                  $label pairs as a definition.
     *
     *                                  AVAILABLE WHEN maxItems > 1:
     *                                  - allowNew bool (FALSE): If set new records can be created with the new record
     *                                  wizard
     *                                  - allowEdit bool (TRUE): Can be used to disable the editing of records in the
     *                                  current group
     *
     *                                  LEGACY SUPPORT
     *                                  - mmTableName string: When given this table name is set as mm table name instead
     *                                  of the automatically generated one. Useful for legacy codebase.
     *
     *                                  DEPRECATED: Will be removed in v12
     *                                  - basePid int|string: Can be used to automatically apply the
     *                                  where string for the base pid. If TRUE is provided, the CURRENT pid will be used
     *                                  as constraint. Use the "limitToPids" option instead
     *                                  - default string|number: A default value for your input field
     *                                  use the setDefault() method on a field instead
     */
    public function applyRelationSelect(string $foreignTable, array $options = []): void
    {
        $foreignTable = NamingUtil::resolveTableName($foreignTable);
        $allowsMultiSelect = empty($options['maxItems']) || (is_numeric($options['maxItems']) && $options['maxItems'] > 1);
        
        $o = $this->initializeOptions(array_merge(
            [
                new UserFuncOption(),
                new SelectItemsOption(null, 'additionalItems'),
                new DefaultOption(null, ['string', 'number', 'null']),
                new LimitToPidsOption($foreignTable, null, [
                    'defaultValue' => false,
                ]),
                new LegacyBasePidToLimitToPidsRewriteOption(),
                new MmTableOption($foreignTable),
                new MinMaxItemOption(),
                'where' => [
                    'type' => 'string',
                    'default' => '',
                ],
            ],
            $allowsMultiSelect ? [
                new WizardAllowEditOption(),
                new WizardAllowNewOption(),
            ] : []
        ));
        
        $options = $o->validate($options);
        
        $this->field->addConfig(
            $o->apply([
                'type' => 'select',
                'renderType' => $options['maxItems'] > 1
                    ? 'selectMultipleSideBySide'
                    : 'selectSingle',
                'foreign_table' => $foreignTable,
                'foreign_table_where' => empty($options['where']) ? null : $options['where'],
                'multiple' => false,
                'localizeReferencesAtParentLocalization' => true,
            ])
        );
    }
    
    /**
     * If you are defining a custom field and want to create an mm table for it, you can call this preset
     * and it will create the mm table and the required configuration for you.
     */
    public function applySetMmTable(): void
    {
        $raw = $this->field->getRaw();
        $raw['config'] = $this->addMmTableConfig($raw['config'] ?? [], []);
        $this->field->setRaw($raw);
    }
}
