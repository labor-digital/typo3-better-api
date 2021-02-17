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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\FormEngine\FieldPreset;

use DateTime;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use LaborDigital\T3BA\FormEngine\UserFunc\SlugPrefixProvider;
use LaborDigital\T3BA\FormEngine\UserFunc\SlugPrefixProviderInterface;
use LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use Neunerlei\Options\Options;
use Neunerlei\TinyTimy\DateTimy;

class InputFields extends AbstractFieldPreset
{

    /**
     * Configures the current field as a simple input element
     *
     * @param   array  $options  Additional options for this preset
     *                           - default string: A default value for your input field
     *                           - required, trim, lower, int, email, password, unique, null bool: Any of these values
     *                           can be passed to define their matching "eval" rules
     *                           - maxLength int (2048): The max length of a input (also affects the length of the db
     *                           field)
     *                           - minLength int (0): The min length of a input
     *                           - readOnly bool (FALSE): True to make this field read only
     *
     */
    public function applyInput(array $options = []): void
    {
        // Prepare the options
        $options = Options::make(
            $options,
            $this->addEvalOptions(
                $this->addMinMaxLengthOptions(
                    $this->addReadOnlyOptions(
                        $this->addPlaceholderOption(
                            [
                                'default' => [
                                    'type'    => 'string',
                                    'default' => '',
                                ],
                            ]
                        )
                    )
                )
            )
        );

        // Prepare the config
        $config = ['type' => 'input', 'size' => 39];

        // Apply defaults
        if (! empty($options['default'])) {
            $config['default'] = $options['default'];
        }
        $config = $this->addReadOnlyConfig($config, $options);
        $config = $this->addEvalConfig($config, $options);
        $config = $this->addMaxLengthConfig($config, $options, true);
        $config = $this->addPlaceholderConfig($config, $options);

        // Done
        $this->field->addConfig($config);
    }

    /**
     * Configures this field as either a date or a datetime field.
     * Date fields have their own datepicker.
     *
     * @param   array  $options  Additional options for this preset
     *                           - default string|number|DateTime: A default value for your input field
     *                           - withTime bool (FALSE): If set to true this field can also have the time set, not
     *                           only
     *                           the date
     *                           - asInt bool (FALSE): By default the database value will be written as "datetime"
     *                           type. If you however want the database to store the date as integer you can set this
     *                           to true
     *                           - required, trim bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     */
    public function applyDate(array $options = []): void
    {
        // Prepare options
        $options = Options::make(
            $options,
            $this->addEvalOptions([
                'withTime' => [
                    'type'    => 'bool',
                    'default' => false,
                ],
                'asInt'    => [
                    'type'    => 'bool',
                    'default' => false,
                ],
                'default'  => [
                    'type'    => ['null', 'string', 'number', DateTime::class, DateTimy::class],
                    'default' => null,
                ],
            ], ['required', 'trim'])
        );

        // Set sql statement
        $this->configureSqlColumn(static function (Column $column) use ($options) {
            if ($options['asInt']) {
                $column->setType(new IntegerType())
                       ->setLength(10)
                       ->setDefault(0);
            } else {
                $column
                    ->setType(new DateTimeType())
                    ->setDefault('CURRENT_TIMESTAMP');
            }
        });

        // Prepare the config
        $config = [
            'type'       => 'input',
            'renderType' => 'inputDateTime',
        ];

        if ($options['default'] !== null) {
            $date              = new DateTimy($options['default']);
            $config['default'] = $options['asInt'] ? $date->getTimestamp() : $date->formatSql();
        }

        $options[$options['withTime'] ? 'datetime' : 'date'] = true;

        $config = $this->addEvalConfig($config, $options);

        if (! $options['asInt']) {
            $config['dbType'] = 'datetime';
        }

        // Done
        $this->field->addConfig($config);
    }

    /**
     * Configures the current field as a link selection.
     *
     * @param   array  $options  Additional config options for this preset
     *                           - allowFiles bool (FALSE): True to allow file links
     *                           - allowExternal bool (TRUE): True to allow external URL links
     *                           - allowPages bool (TRUE): True to allow links to pages
     *                           - allowMail bool (FALSE): True to allow links to mails
     *                           - allowFolder bool (FALSE): True to allow links to storage folders
     *                           - default string: A default value for your input field
     *                           - maxLength int (2048): The max length of a link (also affects the length of the db
     *                           field)
     *                           - minLength int (0): The min length of a input
     *                           - hideClutter bool: By default we hide clutter fields like class or params in the link
     *                           browser. If you want those fields to be set, set this to false.
     *                           - required, trim bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     */
    public function applyLink(array $options = []): void
    {
        // Prepare the options
        $options = Options::make(
            $options,
            $this->addEvalOptions(
                $this->addMinMaxLengthOptions(
                    [
                        'allowFiles'    => [
                            'type'    => 'bool',
                            'default' => false,
                        ],
                        'allowExternal' => [
                            'type'    => 'bool',
                            'default' => true,
                        ],
                        'allowPages'    => [
                            'type'    => 'bool',
                            'default' => true,
                        ],
                        'allowMail'     => [
                            'type'    => 'bool',
                            'default' => false,
                        ],
                        'allowFolder'   => [
                            'type'    => 'bool',
                            'default' => false,
                        ],
                        'default'       => [
                            'type'    => 'string',
                            'default' => '',
                        ],
                        'hideClutter'   => [
                            'type'    => 'bool',
                            'default' => true,
                        ],
                    ],
                    2048
                ),
                ['required', 'trim'],
                ['trim' => true]
            )
        );

        // Prepare blinded url types
        $blindFields = [];
        if (! $options['allowFiles']) {
            $blindFields[] = 'file';
        }
        if (! $options['allowExternal']) {
            $blindFields[] = 'url';
        }
        if (! $options['allowPages']) {
            $blindFields[] = 'page';
        }
        if (! $options['allowMail']) {
            $blindFields[] = 'mail';
        }
        if (! $options['allowFolder']) {
            $blindFields[] = 'folder';
        }
        $blindFields = implode(',', $blindFields);

        // Prepare the config
        $config = [
            'type'         => 'input',
            'softref'      => 'typolink,typolink_tag,images,url',
            'renderType'   => 'inputLink',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'blindLinkOptions' => $blindFields,
                        'blindLinkFields'  => $options['hideClutter'] ? 'class,params' : '',
                    ],
                ],
            ],
        ];

        // Apply defaults
        if (! empty($options['default'])) {
            $config['default'] = $options['default'];
        }
        $config = $this->addEvalConfig($config, $options);
        $config = $this->addMaxLengthConfig($config, $options, true);

        // Set the field
        $this->field->addConfig($config);
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
     *                           - default string: A default value for your input field
     *                           - required, uniqueInSite bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     *                           - prefix string: Allows you to define a static prefix to be rendered
     *                           in front of the actual slug part. This can be a string, or a translation label.
     *                           If the (translated) value starts with a "/" the site base will automatically
     *                           prepended to it. Note: This only works if you don't set prefixProvider!
     *                           - prefixProvider string: Allows you to define a custom slug prefix provider class.
     *                           The class must implement the SlugPrefixProviderInterface in order to work.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Slug.html
     * @see SlugPrefixProviderInterface
     */
    public function applySlug(array $fields, array $options = []): void
    {
        $options = Options::make(
            $options,
            $this->addEvalOptions([
                'separator'      => [
                    'type'    => 'string',
                    'default' => '/',
                ],
                'replacements'   => [
                    'type'    => 'array',
                    'default' => ['/' => '-'],
                ],
                'default'        => [
                    'type'    => ['string', 'number'],
                    'default' => '',
                ],
                'prefix'         => [
                    'type'    => 'string',
                    'default' => '',
                ],
                'prefixProvider' => [
                    'type'      => 'string',
                    'default'   => SlugPrefixProvider::class,
                    'validator' => static function (string $class) {
                        if (! class_exists($class)) {
                            return 'The given class: ' . $class . ' does not exist!';
                        }

                        if (! in_array(SlugPrefixProviderInterface::class, class_implements($class), true)) {
                            return 'The given slug prefix provider: ' . $class
                                   . ' must implement the required interface: ' . SlugPrefixProviderInterface::class;
                        }

                        return true;
                    },
                ],
            ], ['required', 'uniqueInSite'])
        );

        // Sadly, TYPO3 sucks hard sometimes... The prefix provider does not get any information for
        // which field it is generating the slug for. So we have to get our prefix there, somehow...
        $prefixMethod = 'getPrefix';
        if ($options['prefix'] !== '' && $options['prefixProvider'] === SlugPrefixProvider::class) {
            $prefixMethod = 'pre_' . bin2hex($options['prefix']);
        }
        
        // Build the configuration
        $config = [
            'type'              => 'slug',
            'generatorOptions'  => [
                'fields'               => $fields,
                'fieldSeparator'       => $options['separator'],
                'prefixParentPageSlug' => false,
                'replacements'         => $options['replacements'],
            ],
            'appearance'        => [
                'prefix'       => $options['prefixProvider'] . '->' . $prefixMethod,
                'staticPrefix' => $options['prefix'],
            ],
            'prependSlash'      => false,
            'fallbackCharacter' => '-',
            'default'           => $options['default'],
            'size'              => 50,
        ];

        if ($this->field->isReadOnly()) {
            $config['renderType'] = 'input';
        }

        $config = $this->addEvalConfig($config, $options);
        $config = $this->addMaxLengthConfig($config, ['maxLength' => 2048], true);

        // Inject the field configuration
        $this->field->addConfig($config);
    }
}
