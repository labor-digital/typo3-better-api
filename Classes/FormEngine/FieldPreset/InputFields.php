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

use DateTime;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use LaborDigital\T3ba\FormEngine\UserFunc\SlugPrefixProvider;
use LaborDigital\T3ba\FormEngine\UserFunc\SlugPrefixProviderInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\DateEvalOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\DefaultOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\EvalOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\InputSizeOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\LegacyReadOnlyOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MinMaxLengthOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\PlaceholderOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use Neunerlei\TinyTimy\DateTimy;

class InputFields extends AbstractFieldPreset
{
    /**
     * The list of link options that can be allowed in a link
     * The key defines the option name, the value 0: the actual option to be blinded, and 1: the default state
     */
    protected const BLINDABLE_LINK_OPTIONS
        = [
            'allowFiles' => ['file', false],
            'allowExternal' => ['url', true],
            'allowPages' => ['page', true],
            'allowMail' => ['mail', false],
            'allowFolder' => ['folder', false],
            'allowPhone' => ['telephone', false],
        ];
    
    /**
     * Configures the current field as a simple input element
     *
     * @param   array  $options  Additional options for this preset
     *                           - required, trim, lower, int, email, password, unique, null bool: Any of these values
     *                           can be passed to define their matching "eval" rules
     *                           - maxLength int (2048): The max length of an input (also affects the length of the db
     *                           field)
     *                           - minLength int (0): The min length of an input
     *                           - size int|string (100%) Defines the width of an input inside its column.
     *                           Can be either an integer from 10-50 or a percentage from 0-100 suffixed by
     *                           the "%" sign, as a string.
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - readOnly bool (FALSE): True to make this field read only
     *                           use the setReadOnly() method on a field instead
     *                           - default string|array: A default value for your input field. Can be a callback
     *                           in form of an array like [class, method] as well.
     *                           use the setDefault() method on a field instead
     */
    public function applyInput(array $options = []): void
    {
        $o = $this->initializeOptions(
            [
                new InputSizeOption(),
                new DefaultOption(),
                new PlaceholderOption(),
                new LegacyReadOnlyOption(),
                new MinMaxLengthOption(null, null, true),
                new EvalOption(),
            ]
        );
        
        $o->validate($options);
        
        $this->field->addConfig(
            $o->apply([
                'type' => 'input',
                'renderType' => '__UNSET',
            ])
        );
    }
    
    /**
     * Configures this field as either a date or a datetime field.
     * Date fields have their own datepicker.
     *
     * @param   array  $options  Additional options for this preset
     *                           - withTime bool (FALSE): If set to true this field can also have the time set, not
     *                           only the date
     *                           - asInt bool (TRUE): By default the database value will be written as "integer"
     *                           type. If you however want the database to store the date as datetime you can set this
     *                           to false
     *                           - required, trim bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     *                           - size int|string (100%) Defines the width of an input inside its column.
     *                           Can be either an integer from 10-50 or a percentage from 0-100 suffixed by
     *                           the "%" sign, as a string.
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - default string|number|DateTime: A default value for your input field
     *                           use the setDefault() method on a field instead
     */
    public function applyDate(array $options = []): void
    {
        $o = $this->initializeOptions([
            'withTime' => [
                'type' => 'bool',
                'default' => false,
            ],
            'asInt' => [
                'type' => 'bool',
                'default' => true,
            ],
            new InputSizeOption(),
            new DefaultOption(null, ['null', 'string', 'number', DateTime::class, DateTimy::class]),
            new DateEvalOption(),
        ]);
        
        $options = $o->validate($options);
        
        $this->context->configureSqlColumn(
            static function (Column $column) use ($options) {
                if ($options['asInt']) {
                    $column->setType(new IntegerType())
                           ->setLength(10)
                           ->setDefault(0);
                } else {
                    $column
                        ->setType(new DateTimeType())
                        ->setDefault('CURRENT_TIMESTAMP');
                }
            }
        );
        
        $config = $o->apply(
            [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'dbType' => $options['asInt'] ? null : 'datetime',
            ]
        );
        
        // As this field is special, we need to override the default value to add the value in the correct format
        if ($options['default'] !== null) {
            $date = new DateTimy($options['default']);
            $config['default'] = $options['asInt'] ? $date->getTimestamp() : $date->formatSql();
        }
        
        $this->field->addConfig($config);
    }
    
    /**
     * Configures the current field as a link selection.
     *
     * @param   array  $options  Additional config options for this preset
     *                           - allowLinkSets bool|array (FALSE): True to allow all link sets that were added
     *                           to the link browser, false to disable all link sets (default), or an array
     *                           of specific link sets that should be allowed for this field
     *                           - allowFiles bool (FALSE): True to allow file links
     *                           - allowExternal bool (TRUE): True to allow external URL links
     *                           - allowPages bool (TRUE): True to allow links to pages
     *                           - allowMail bool (FALSE): True to allow links to mails
     *                           - allowFolder bool (FALSE): True to allow links to storage folders
     *                           - allowPhone bool (FALSE): True to allow telephone numbers
     *                           - default string: A default value for your input field
     *                           - maxLength int (2048): The max length of a link (also affects the length of the db field)
     *                           - minLength int (0): The min length of a input
     *                           - blindFields array|true (["class", "params"]):
     *                           Defines which link option fields should be hidden or shown. TRUE to hide ALL fields,
     *                           or an array of fields to be hidden. "class" and "params" are blinded by default,
     *                           pass an empty array to always show them.
     *                           - required, trim bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     *                           - size int|string (100%) Defines the width of an input inside its column.
     *                           Can be either an integer from 10-50 or a percentage from 0-100 suffixed by
     *                           the "%" sign, as a string.
     *
     *                           DEPRECATED: Will be removed in v11, use "blindFields" instead
     *                           - hideClutter bool: By default we hide clutter fields like class or params in the link
     *                           browser. If you want those fields to be set, set this to false.
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - default string|array: A default value for your input field. Can be a callback
     *                           in form of an array like [class, method] as well.
     *                           use the setDefault() method on a field instead
     *
     * @todo remove hideClutter in the next major release
     */
    public function applyLink(array $options = []): void
    {
        $o = $this->initializeOptions(
            array_merge(
                
                array_map(static function (array $def): array {
                    return [
                        'type' => 'bool',
                        'default' => $def[1],
                    ];
                }, static::BLINDABLE_LINK_OPTIONS),
                
                [
                    'allowLinkSets' => [
                        'type' => ['bool', 'array'],
                        'default' => false,
                    ],
                    // @todo remove this in the next major release
                    'hideClutter' => [
                        'type' => 'bool',
                        'default' => true,
                    ],
                    'blindFields' => [
                        'type' => ['array', 'true', 'null'],
                        'default' => null,
                        'filter' => static function ($value, $_, array $options) {
                            if (is_array($value)) {
                                return $value;
                            }
                            
                            if ($value === true) {
                                return ['class', 'params', 'target', 'title'];
                            }
                            
                            // @todo remove this in the next major release
                            if (! $options['hideClutter']) {
                                return [];
                            }
                            
                            return ['class', 'params'];
                        },
                    ],
                    
                    new InputSizeOption(),
                    new DefaultOption(),
                    new MinMaxLengthOption(2048, null, true),
                    new EvalOption(['required', 'trim'], ['trim' => true]),
                ]
            )
        );
        
        $options = $o->validate($options);
        
        // Handle "hideClutter" deprecation
        // @todo remove this in the next major release
        if (! $options['hideClutter']) {
            $table = $this->field->getForm()->getTableName();
            $field = $this->field->getId();
            trigger_error(
                'Deprecated option in: ' . $table . '::' . $field . '. The "hideClutter" option will be removed in v11, use the "blindFields" option instead',
                E_USER_DEPRECATED
            );
        }
        
        $blindOptions = array_filter(
            array_values(
                array_map(static function (string $key, array $def) use ($options): ?string {
                    return $options[$key] ? null : $def[0];
                }, array_keys(static::BLINDABLE_LINK_OPTIONS), static::BLINDABLE_LINK_OPTIONS)
            )
        );
        $blindOptions[] = '@linkSets:' . urlencode(SerializerUtil::serializeJson($options['allowLinkSets']));
        
        $this->field->addConfig(
            $o->apply(
                [
                    'type' => 'input',
                    'softref' => 'typolink,typolink_tag,images,url',
                    'renderType' => 'inputLink',
                    'fieldControl' => [
                        'linkPopup' => [
                            'options' => [
                                'blindLinkOptions' => implode(',', $blindOptions),
                                'blindLinkFields' => implode(',', $options['blindFields']),
                            ],
                        ],
                    ],
                ]
            )
        );
    }
    
    /**
     * Superset of the "link" preset, preconfigured for the use of phone numbers.
     *
     * @param   array  $options  Additional options inherited from the link preset
     *                           - maxLength int (128): The max length of a link (also affects the length of the db field)
     *                           - minLength int (0): The min length of a input
     *                           - blindFields array|true (["class", "params"]):
     *                           Defines which link option fields should be hidden or shown. TRUE to hide ALL fields,
     *                           or an array of fields to be hidden. "class" and "params" are blinded by default,
     *                           pass an empty array to always show them.
     *                           - required, trim bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - default string|array: A default value for your input field. Can be a callback
     *                           in form of an array like [class, method] as well.
     *                           use the setDefault() method on a field instead
     */
    public function applyLinkPhone(array $options = []): void
    {
        $this->applyLink(
            array_merge(
                [
                    'maxLength' => 128,
                    'allowExternal' => false,
                    'allowPages' => false,
                    'allowPhone' => true,
                    'blindFields' => true,
                ],
                $options
            )
        );
    }
    
    /**
     * Converts your field into a slug or path segment field. By default we will use a custom renderer
     * to make sure your slug's don't look like "www.your-domain.deyour-slug". If you want the default
     * behaviour set the "useNativeElement" flag to true.
     *
     * @param   array  $fields   The list of fields from which the slug should be generated.
     *                           Multiple fields will be concatenated like described in the TCA configuration.
     * @param   array  $options  Additional configuration options
     *                           - replacements array (["/" => "-"]): A list of characters that should be replaced
     *                           with another character when the slug is generated. By default we remove all slashes
     *                           and turn them into dashes.
     *                           - separator string (/): Used to separate the values of certain fields
     *                           from one another.
     *                           - required, uniqueInSite bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     *                           - prefix string: Allows you to define a static prefix to be rendered
     *                           in front of the actual slug part. This can be a string, or a translation label.
     *                           If the (translated) value starts with a "/" the site base will be automatically
     *                           prepended to it. Note: This only works if you don't set prefixProvider!
     *                           - prefixProvider string: Allows you to define a custom slug prefix provider class.
     *                           The class must implement the SlugPrefixProviderInterface in order to work.
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - default string|array: A default value for your input field. Can be a callback
     *                           in form of an array like [class, method] as well.
     *                           use the setDefault() method on a field instead
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Slug.html
     * @see SlugPrefixProviderInterface
     */
    public function applySlug(array $fields, array $options = []): void
    {
        $o = $this->initializeOptions([
            'separator' => [
                'type' => 'string',
                'default' => '/',
            ],
            'replacements' => [
                'type' => 'array',
                'default' => ['/' => '-'],
            ],
            'prefix' => [
                'type' => 'string',
                'default' => '',
            ],
            'prefixProvider' => [
                'type' => 'string',
                'default' => SlugPrefixProvider::class,
                'validator' => static function (string $class) {
                    if (! class_exists($class)) {
                        return 'The given class: ' . $class . ' does not exist!';
                    }
                    
                    if (! in_array(SlugPrefixProviderInterface::class, class_implements($class), true)) {
                        return 'The given slug prefix provider: ' . $class
                               . ' must implement the required interface: '
                               . SlugPrefixProviderInterface::class;
                    }
                    
                    return true;
                },
            ],
            
            new DefaultOption('', ['string', 'number']),
            new EvalOption(['required', 'uniqueInSite']),
            new MinMaxLengthOption(2048, null, true),
            new InputSizeOption(),
        ]);
        
        $options = $o->validate($options);
        
        // Sadly, TYPO3 sucks hard sometimes... The prefix provider does not get any information for
        // which field it is generating the slug for. So we have to get our prefix there, somehow...
        $prefixMethod = 'getPrefix';
        if ($options['prefix'] !== '' && $options['prefixProvider'] === SlugPrefixProvider::class) {
            $prefixMethod = 'pre_' . bin2hex($options['prefix']);
        }
        
        $this->field->addConfig(
            $o->apply([
                'type' => 'slug',
                'renderType' => $this->field->isReadOnly() ? 'input' : null,
                'generatorOptions' => [
                    'fields' => $fields,
                    'fieldSeparator' => $options['separator'],
                    'prefixParentPageSlug' => false,
                    'replacements' => $options['replacements'],
                ],
                'appearance' => [
                    'prefix' => $options['prefixProvider'] . '->' . $prefixMethod,
                    'staticPrefix' => $options['prefix'],
                ],
                'prependSlash' => false,
                'fallbackCharacter' => '-',
            ])
        );
    }
}
