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


namespace LaborDigital\T3ba\Tool\Tca\ContentType\Builder;

use InvalidArgumentException;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\AbstractDataModel;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\DefaultDataModel;

class ContentType extends TcaTableType
{
    /**
     * The name of the class to use as data content model. The data model is an extbase model for the data of a single
     * content element. Therefore it behaves in the same way a normal extbase entity does.
     *
     * @var string
     */
    protected $dataModelClass = DefaultDataModel::class;
    
    /**
     * Contains the variant name of a content element/plugin which is currently configured.
     * This allows you to create a switch inside your type configuration method.
     * Null if the default variant is being configured
     *
     * @var string|null
     */
    protected $variant;
    
    /**
     * Returns the name of the class to use as data model.
     *
     * @return string
     */
    public function getDataModelClass(): string
    {
        return $this->dataModelClass;
    }
    
    /**
     * Allows you to configure the content model class to use when the data is retrieved. The data content model is an
     * extbase model for the data of a single content element. Therefore it behaves in the same way a normal extbase
     * entity does.
     *
     * @param   string  $modelClass  The name of the class, which extends AbstractDataModel
     *
     * @return ContentType
     */
    public function setDataModelClass(string $modelClass): ContentType
    {
        if (! class_exists($modelClass) || ! in_array(AbstractDataModel::class, class_parents($modelClass), true)) {
            throw new InvalidArgumentException(
                'The given model class ' . $modelClass . ' must extend ' . AbstractDataModel::class);
        }
        $this->dataModelClass = $modelClass;
        
        return $this;
    }
    
    /**
     * Returns the content element signature / CType this form is linked with
     *
     * @return string
     */
    public function getSignature(): string
    {
        return $this->typeName;
    }
    
    /**
     * Returns the variant name of a content element/plugin which is currently configured.
     * This allows you to create a switch inside your type configuration method.
     * Null if the default variant is being configured
     *
     * @return string|null
     */
    public function getVariant(): ?string
    {
        return $this->variant;
    }
    
    /**
     * Internal helper to inject the variant into the given type object
     *
     * @param   ContentType  $type
     * @param   string       $variant
     *
     * @internal
     */
    public static function bindVariant(ContentType $type, string $variant): void
    {
        $type->variant = $variant;
    }
}
