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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\UserFunc;


use InvalidArgumentException;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;

class SlugPrefixProvider implements SlugPrefixProviderInterface
{
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function getPrefix(array $context, TcaSlug $slug): string
    {
        return static::getUrlBaseForContext($context) . '/slug/of/the/page';
    }
    
    /**
     * This is the other end of our rube goldberg machine to transport a static prefix
     * through the tca slug provider.
     *
     * @param $name
     * @param $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'pre_') !== 0) {
            return '';
        }
        $prefix = hex2bin(substr($name, 4));
        
        $prefix = $this->cs()->translator->translate($prefix);
        
        if (strpos($prefix, '/') === 0) {
            $prefix = static::getUrlBaseForContext($arguments[0]) . $prefix;
        }
        
        return rtrim($prefix, '/');
    }
    
    /**
     * Render the prefix for the input field.
     *
     * @param   array  $context
     *
     * @return string
     */
    public static function getUrlBaseForContext(array $context): string
    {
        $site = $context['site'];
        $languageId = $context['languageId'];
        try {
            $language = ($languageId < 0) ? $site->getDefaultLanguage() : $site->getLanguageById($languageId);
            $base = $language->getBase();
            $prefix = rtrim((string)$base, '/');
            if ($prefix !== '' && empty($base->getScheme()) && $base->getHost() !== '') {
                $prefix = 'http:' . $prefix;
            }
        } catch (InvalidArgumentException $e) {
            $prefix = '';
        }
        
        return $prefix;
    }
}
