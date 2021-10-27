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
 * Last modified: 2021.10.25 at 14:10
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;

/**
 * Option to apply the "maxLength" configuration to the config array of an input field
 */
class MinMaxLengthOption extends AbstractOption
{
    /**
     * The default value of the maximal input length
     *
     * @var int
     */
    protected $defaultMax;
    
    /**
     * The default value for the minimal input length
     *
     * @var int
     */
    protected $defaultMin;
    
    /**
     * If set to true the sql statement of this column will automatically be adjusted to the maximum input length.
     * This makes only sense for text fields
     *
     * @var bool
     */
    protected $addSqlStatement;
    
    public function __construct(?int $defaultMax = null, ?int $defaultMin = null, bool $addSqlStatement = false)
    {
        $this->defaultMax = $defaultMax ?? 512;
        $this->defaultMin = $defaultMin ?? 0;
        $this->addSqlStatement = $addSqlStatement;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['maxLength'] = [
            'type' => 'int',
            'default' => $this->defaultMax,
        ];
        
        $definition['minLength'] = [
            'type' => 'int',
            'default' => $this->defaultMin,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (! empty($options['maxLength'])) {
            $config['max'] = $options['maxLength'];
        }
        
        if (! empty($options['minLength'])) {
            $config['min'] = $options['minLength'];
        }
        
        if ($this->addSqlStatement) {
            $this->context->configureSqlColumn(
                static function (Column $column) use ($options) {
                    if ((int)$options['maxLength'] <= 4096) {
                        $column->setType(new StringType())
                               ->setDefault('')
                               ->setNotnull(true)
                               ->setLength((int)$options['maxLength']);
                    } else {
                        $column->setType(new TextType())
                               ->setNotnull(false)
                               ->setDefault(null)
                               ->setLength(null);
                    }
                }
            );
        }
    }
    
}