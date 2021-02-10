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
 * Last modified: 2021.01.27 at 16:14
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table;


use Doctrine\DBAL\Schema\Column;
use LaborDigital\T3BA\Tool\Sql\ColumnAdapter;
use LaborDigital\T3BA\Tool\Sql\FallbackType;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits\LayoutMetaTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits\TcaDataHookCollectorAddonTrait;
use Neunerlei\Arrays\Arrays;

class TcaField extends AbstractField
{
    use LayoutMetaTrait;
    use TcaDataHookCollectorAddonTrait;

    /**
     * Holds the flexForm configuration if there is one
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaFieldFlexFormConfig
     */
    protected $flex;

    /**
     * Returns the database table name for the current field
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->getForm()->getTableName();
    }

    /**
     * Returns the database name of the current field
     *
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->getId();
    }

    /**
     * Returns the flex form configuration for this field.
     *
     * Attention: If you use this method your field will automatically converted into a flex field!
     * If you don't convert the field automatically, but check first: take a look at the hasFlexFormConfig() method.
     *
     * @return TcaFieldFlexFormConfig
     */
    public function getFlexFormConfig(): TcaFieldFlexFormConfig
    {
        // Check if we already have a flex form config
        if (isset($this->flex)) {
            return $this->flex;
        }

        // Create new flex form config
        $cs = $this->getRoot()->getContext()->cs();

        return $this->flex = $cs->typoContext->di()->getWithoutDi(
            TcaFieldFlexFormConfig::class, [$this, $this->config, $cs->flexFormFactory]
        );
    }

    /**
     * Returns true if the current field is considered a "flexForm" field, false if not
     *
     * @return bool
     */
    public function isFlexForm(): bool
    {
        return $this->flex || $this->config['config']['type'] === 'flex';
    }

    /**
     * @inheritDoc
     */
    public function setRaw(array $raw)
    {
        // Store ds values to allow automatic config flushing
        $dsOld = json_encode(Arrays::getPath($this->config, 'config.[ds,ds_pointerField]'), JSON_THROW_ON_ERROR);

        // Load flex form configuration
        if ($raw['config']['type'] === 'flex' && ! empty($this->flex)) {
            $dsNew = json_encode(Arrays::getPath($raw, 'config.[ds,ds_pointerField]'), JSON_THROW_ON_ERROR);
            // Reset the flex configuration
            if ($dsNew !== $dsOld) {
                $this->flex = null;
            }
        } elseif ($raw['config']['type'] !== 'flex') {
            $this->flex = null;
        }

        return parent::setRaw($raw);
    }

    /**
     * @inheritDoc
     */
    public function getRaw(): array
    {
        $raw = parent::getRaw();

        if ($this->flex) {
            $this->flex->dump($raw);
        }

        return $raw;
    }

    /**
     * @inheritDoc
     */
    public function inheritFrom(AbstractField $field)
    {
        parent::inheritFrom($field);

        // Automatically inherit the SQL definition
        if (method_exists($field, 'getColumn')) {
            ColumnAdapter::inheritConfig($this->getColumn(), $field->getColumn());
        }
    }

    /**
     * Returns the sql definition for this field
     *
     * @return \Doctrine\DBAL\Schema\Column
     */
    public function getColumn(): Column
    {
        return $this->getRoot()->getContext()->cs()->sqlRegistry->getColumn(
            $this->getTableName(), $this->getForm()->getTypeName(), $this->getId()
        );
    }

    /**
     * Removes this field from the sql table.
     * You should only use this if you have a display-only field that should not store any data for itself
     *
     * @return $this
     */
    public function removeColumn(): self
    {
        $this->getColumn()->setType(new FallbackType());

        return $this;
    }
}
