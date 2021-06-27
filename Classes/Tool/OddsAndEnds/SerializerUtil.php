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
 * Last modified: 2021.06.04 at 16:22
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\OddsAndEnds;


use Neunerlei\Arrays\Arrays;
use Throwable;

class SerializerUtil
{
    /**
     * A wrapper around serialize() for completeness sake
     *
     * @param   mixed  $value  The value to serialize
     *
     * @return string
     * @see \serialize()
     */
    public static function serialize($value): string
    {
        return serialize($value);
    }
    
    /**
     * Wrapper around the unserialize() function to convert a stringified data structure back into its initial data type
     *
     * @param   null|string              $data          The data that should be unserialized
     * @param   null|false|array|string  $allowObjects  A list of classes that should be converted back into objects.
     *                                                  By default all classes are allowed. Possible values are:
     *                                                  - false Disables all object instantiation
     *                                                  - string A comma separated list of classes to instantiate
     *                                                  - array an array of classes to instantiate
     *                                                  This will override "allowed_classes" in options
     * @param   array                    $options       Additional options for unserialize
     *
     * @return mixed
     * @throws \LaborDigital\T3ba\Tool\OddsAndEnds\BrokenSerializedDataException
     * @see \unserialize()
     */
    public static function unserialize(?string $data, $allowObjects = null, array $options = [])
    {
        if (! is_string($data)) {
            return null;
        }
        
        if ($allowObjects === false) {
            $allowObjects = [];
        } elseif (is_string($allowObjects)) {
            $allowObjects = Arrays::makeFromStringList($allowObjects);
        } elseif (! is_array($allowObjects)) {
            $allowObjects = null;
        }
        
        if ($allowObjects !== null) {
            $options['allowed_classes'] = array_unique($allowObjects);
        }
        
        $result = unserialize($data, $options);
        
        // Automatically try to fix incorrect string lengths by taking multibyte chars into account
        if ($result === false && serialize(false) !== $data) {
            $fixedData = preg_replace_callback('!s:(\d+):"(.*?)";!', static function ($match) {
                return $match[1] === strlen($match[2]) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
            }, $data);
            
            $result = unserialize($fixedData, $options);
            
            if ($result === false) {
                $message = 'Failed to unserialize serialized data: "' . substr($data, 0, 256) . (strlen($data) > 256 ? '...' : '') . '"';
                throw new BrokenSerializedDataException($message);
            }
        }
        
        return $result;
    }
    
    /**
     * Dumps the given value as JSON string.
     *
     * @param   mixed  $value    The value to convert to json
     * @param   array  $options  Additional configuration options for the encoding
     *                           - pretty bool (FALSE): If set to TRUE the JSON will be generated pretty printed
     *                           - options int (0): Bitmask consisting of one or multiple of the JSON_ constants.
     *                           The behaviour of these constants is described on the JSON constants page.
     *                           JSON_THROW_ON_ERROR is set by default for all operations
     *                           - depth int (512): User specified recursion depth.
     *
     * @return string
     * @throws \JsonException if the encoding fails
     * @see          \json_encode() for possible options
     * @see          https://php.net/manual/en/function.json-encode.php
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public static function serializeJson($value, array $options = []): string
    {
        $jsonOptions = $options['options'] ?? 0;
        $jsonOptions |= JSON_THROW_ON_ERROR;
        if (($options['pretty'] ?? false) || in_array('pretty', $options, true)) {
            $jsonOptions |= JSON_PRETTY_PRINT;
        }
        
        /** @noinspection JsonEncodingApiUsageInspection */
        return json_encode($value, $jsonOptions, (int)($options['depth'] ?? 512));
    }
    
    /**
     * Creates an object out of a json data string. Throws an exception if an error occurred!
     * It automatically uses the assoc option when objects are unserialized
     *
     * @param   null|string  $data     The data to convert to json
     * @param   array        $options  Additional configuration options for the encoding
     *                                 - assoc bool (TRUE): By default objects are unserialized as associative arrays
     *                                 - options int (0): Bitmask consisting of one or multiple of the JSON_ constants.
     *                                 The behaviour of these constants is described on the JSON constants page.
     *                                 JSON_THROW_ON_ERROR is set by default for all operations
     *                                 - depth int (512): User specified recursion depth.
     *
     * @return mixed
     * @throws \LaborDigital\T3ba\Tool\OddsAndEnds\BrokenSerializedDataException if the decoding fails
     * @see          \json_encode() for possible options
     * @see          https://php.net/manual/en/function.json-encode.php
     */
    public static function unserializeJson(?string $data, array $options = [])
    {
        if (! is_string($data)) {
            return null;
        }
        
        $jsonOptions = $options['options'] ?? 0;
        $jsonOptions |= JSON_THROW_ON_ERROR;
        
        try {
            /** @noinspection JsonEncodingApiUsageInspection */
            return @json_decode(
                $data, (bool)($options['assoc'] ?? true), (int)($options['depth'] ?? 512), $jsonOptions
            );
        } catch (Throwable $e) {
            $message = 'Failed to unserialize JSON data: "' . substr($data, 0, 256) . (strlen($data) > 256 ? '...' : '') . '"';
            throw new BrokenSerializedDataException($message, 0, $e);
        }
    }
}