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


namespace LaborDigital\T3ba\Tool\Cache\Util;


use LaborDigital\T3ba\Tool\Cache\CacheTagProviderInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\PathUtil\Path;
use Throwable;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class CacheUtil
{
    /**
     * Converts the given value into a list of cache tags.
     * see the $tag parameter for all allowed types that can be used as a tag.
     *
     * - Strings and Numbers: Kept as they are
     * - CacheTagAwareInterface: Use the getCacheTag() method
     * - AbstractEntity: Use the matching table name and storage pid
     * - Object: Try to find a getCacheTag() method
     * - Object (alternative): Try to find a getPid() method and combine it with the object class
     * - Object (fallback): try to serialize or json encode the value as an md5
     * - Fallback: If no string tag could be calculated NULL is returned
     *
     * @param   string|int|CacheTagProviderInterface|AbstractEntity|object|null  $tag  The value to convert into a tag.
     *
     * @return array
     */
    public static function stringifyTag($tag): array
    {
        if (empty($tag) && $tag !== 0) {
            return [];
        }
        
        if (is_string($tag) || is_numeric($tag)) {
            return [(string)$tag];
        }
        
        if (is_object($tag)) {
            $tags = [];
            if ($tag instanceof CacheTagProviderInterface || method_exists($tag, 'getCacheTags')) {
                $_tags = $tag->getCacheTags();
                if (is_array($_tags)) {
                    $tags = array_merge($tags, $_tags);
                }
            }
            
            if (method_exists($tag, 'getPid')) {
                $tags[] = 'page_' . $tag->getPid();
            }
            
            if ($tag instanceof AbstractEntity) {
                $tags[] = NamingUtil::resolveTableName($tag) . '_' . $tag->getUid();
            } else {
                $tags[] = Path::classBasename(get_class($tag)) . '_' . md5(get_class($tag));
            }
            
            if (! empty($tags)) {
                return $tags;
            }
        }
        
        try {
            return [gettype($tag) . '_' . md5(serialize($tag))];
        } catch (Throwable $e) {
            try {
                return [gettype($tag) . '_' . md5(\GuzzleHttp\json_encode($tag))];
            } catch (Throwable $e) {
                return [];
            }
        }
    }
}
