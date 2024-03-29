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


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common;


use InvalidArgumentException;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\PathUtil\Path;

/**
 * Trait SignaturePluginNameMapTrait
 *
 * Used in config handlers to create the signature-plugin name map for extbase plugins/modules/content elements
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common
 */
trait SignaturePluginNameMapTrait
{
    
    /**
     * The local map of stored values
     *
     * @var array
     */
    protected $signaturePluginNameMap = [];
    
    /**
     * Helper to generate an extbase signature out of a given controller class name
     *
     * @param   string  $elementClass
     * @param   string  $elementKey
     * @param   bool    $hasKeyProvider
     *
     * @return string
     */
    protected function getSignatureFromClass(string $elementClass, string $elementKey, bool $hasKeyProvider): string
    {
        $name = preg_replace('/Controller$/i', '', Path::classBasename($elementClass));
        if ($hasKeyProvider) {
            $signature = $elementKey;
        } else {
            $signature = NamingUtil::pluginSignature($name, $this->context->getExtKey());
        }
        
        if (! isset($this->signaturePluginNameMap[$signature])) {
            $this->signaturePluginNameMap[$signature] = $name;
        }
        
        return $signature;
    }
    
    /**
     * Returns the plugin name for the given signature
     *
     * @param   string  $signature
     *
     * @return string
     */
    protected function getPluginNameForSignature(string $signature): string
    {
        if (! isset($this->signaturePluginNameMap[$signature])) {
            throw new InvalidArgumentException(
                'There is no plugin name for signature: ' . $signature . ' registered!');
        }
        
        return $this->signaturePluginNameMap[$signature];
    }
}
