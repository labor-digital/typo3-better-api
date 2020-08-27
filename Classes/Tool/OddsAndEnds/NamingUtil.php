<?php
declare(strict_types=1);
/*
 * Copyright 2020 LABOR.digital
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

namespace LaborDigital\T3BA\Tool\OddsAndEnds;

use InvalidArgumentException;
use Neunerlei\PathUtil\Path;

class NamingUtil
{

    /**
     * Receives the class of a plugin / module controller and returns the matching plugin name
     *
     * @param   string  $controllerClass
     *
     * @return string
     */
    public static function pluginNameFromControllerClass(string $controllerClass): string
    {
        $pluginName = Path::classBasename($controllerClass);

        return preg_replace('/Controller$/i', '', $pluginName);
    }

    /**
     * Receives a plugin name and a extension key and returns the plugin signature which will look like
     * "myextension_mypluginname" Note: Vendors are not allowed in the extKey while defining plugin signatures, so we
     * will automatically strip potential vendors from the extKey.
     *
     * @param   string  $pluginName
     * @param   string  $extKey
     *
     * @return string
     */
    public static function pluginSignature(string $pluginName, string $extKey): string
    {
        return static::flattenExtKey($extKey) . '_' . static::flattenExtKey($pluginName, true);
    }

    /**
     * This will flatten the extension key down for the usage in plugin signatures like:
     * "Vendor.My_Extension" becomes: "myextension". In some cases, like for our typoScript injection
     * we want to keep the vendor to flatten to something like: "vendormyextension" for which the $keepVendor option is
     * present.
     *
     * @param   string  $extKey      The extension key to process
     * @param   bool    $keepVendor  True to keep the vendor in your extension key
     *
     * @return string
     */
    public static function flattenExtKey(string $extKey, bool $keepVendor = false): string
    {
        if (! $keepVendor) {
            $extKey = static::extkeyWithoutVendor($extKey);
        }

        return strtolower(str_replace(['_', ' ', '.'], '', trim($extKey)));
    }

    /**
     * Receives the ext key, which may include a vendor like "vendor.my_extension" and strips off the vendor
     * which results in "my_extension". It also accepts a plain extKey like "my_extension" which will
     * be passed trough without problems.
     *
     * @param   string  $extKey
     *
     * @return string
     */
    public static function extKeyWithoutVendor(string $extKey): string
    {
        if (strpos($extKey, '.') === false) {
            return $extKey;
        }

        return substr($extKey, strpos($extKey, '.') + 1);
    }

    /**
     * Receives the ext key, which may include a vendor like "vendor.my_extension". If it contains a vendor, "vendor"
     * will be returned. If an extKey like "my_extension" is passed, an empty string is returned instead.
     *
     * @param   string  $extkey
     *
     * @return string
     */
    public static function vendorFromExtKey(string $extkey): string
    {
        if (strpos($extkey, '.') === false) {
            return '';
        }

        return substr($extkey, 0, strpos($extkey, '.'));
    }

    /**
     * Receives a typo callback like namespace\\class->method
     * and converts it into an array of ["class"=>"namespace\\class", "method" => "method"]
     *
     * @param   string  $callback  The callback to be parsed
     *
     * @return array
     */
    public static function typoCallbackToArray(string $callback): array
    {
        // Concatinated, multi callbacks -> flexform
        if (strpos($callback, ';') !== false) {
            return array_map(
                [static::class, 'typoCallbackToArray'],
                array_filter(array_map('trim', explode(';', $callback)))
            );
        }
        $callbackParts = explode('->', $callback);
        if (count($callbackParts) !== 2) {
            throw new InvalidArgumentException(
                'Invalid TypoCallback given: "' . $callback . '". It has to be something like: namespace\\class->method'
            );
        }

        return [
            'class'  => trim(str_replace('/', '\\', $callbackParts[0]), '\\ '),
            'method' => trim($callbackParts[1], ' \\/()'),
        ];
    }
}
