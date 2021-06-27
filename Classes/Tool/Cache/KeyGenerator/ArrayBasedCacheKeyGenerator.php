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
 * Last modified: 2021.06.04 at 16:19
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Cache\KeyGenerator;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Arrays\Arrays;
use Throwable;

class ArrayBasedCacheKeyGenerator implements CacheKeyGeneratorInterface, NoDiInterface
{
    /**
     * Contains the data that is used to generate the cache key with
     *
     * @var array
     */
    protected $data;
    
    /**
     * ArrayBasedCacheKeyGenerator constructor.
     *
     * @param   array  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * @inheritDoc
     */
    public function makeCacheKey(): string
    {
        $dataList = Arrays::flatten($this->data);
        ksort($dataList);
        
        try {
            return md5(SerializerUtil::serialize($dataList));
        } catch (Throwable $exception) {
            return md5(SerializerUtil::serializeJson($dataList));
        }
    }
    
}
