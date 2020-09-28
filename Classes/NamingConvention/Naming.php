<?php
/**
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
 * Last modified: 2020.03.19 at 01:41
 */

namespace LaborDigital\Typo3BetterApi\NamingConvention;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

class Naming
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

        return preg_replace('/Controller$/si', '', $pluginName);
    }

    /**
     * Receives a plugin name and a extension key and returns the plugin signature which will look like
     * "myextension_mypluginname" Note: Vendors are not allowed in the extkey when defining plugin signatures, so we
     * will automatically strip potential vendors from the extkey.
     *
     * @param   string  $pluginName
     * @param   string  $extkey
     *
     * @return string
     */
    public static function pluginSignature(string $pluginName, string $extkey): string
    {
        return static::flattenExtKey($extkey) . '_' . static::flattenExtKey($pluginName, true);
    }

    /**
     * This will flatten the extension key down for the usage in plugin signatures like:
     * "Vendor.My_Extension" => "myextension". In some cases, like for our typoscript injection
     * we want to keep the vendor to flatten to sometik like: "vendormyextension" for which the $keepVendor option is
     * present.
     *
     * @param   string  $extkey
     * @param   bool    $keepVendor  True keepy the vendor in your extkey
     *
     * @return string
     */
    public static function flattenExtKey(string $extkey, bool $keepVendor = false): string
    {
        if (! $keepVendor) {
            $extkey = static::extkeyWithoutVendor($extkey);
        }

        return strtolower(str_replace(['_', ' ', '.'], '', trim($extkey)));
    }

    /**
     * Receives the ext key, which may include a vendor like "vendor.my_extension" and strips off the vendor
     * which results in "my_extension". It also accepts a plain extkey like "my_extension" which will
     * be passed trough without poroblem.
     *
     * @param   string  $extkey
     *
     * @return string
     */
    public static function extkeyWithoutVendor(string $extkey): string
    {
        if (strpos($extkey, '.') === false) {
            return $extkey;
        }

        return substr($extkey, strpos($extkey, '.') + 1);
    }

    /**
     * Receives the ext key, which may include a vendor like "vendor.my_extension". If it contains a vendor, "vendor"
     * will be returned. If an extkey like "my_extension" is passed, an empty string is returned instead.
     *
     * @param   string  $extkey
     *
     * @return string
     */
    public static function vendorFromExtkey(string $extkey): string
    {
        if (strpos($extkey, '.') === false) {
            return '';
        }

        return substr($extkey, 0, strpos($extkey, '.'));
    }

    /**
     * Finds the database table name for the given extbase model class
     *
     * @param   string  $modelClass  The name of the extbase model we should find the table name for
     *
     * @return string
     */
    public static function tableNameFromModelClass(string $modelClass): string
    {
        return TypoContainer::getInstance()->get(DataMapper::class)->getDataMap($modelClass)->getTableName();
    }

    /**
     * Recieves a typo callback like namespace\\class->method
     * and converts it into an array of ["class"=>"namespace\\class", "method" => "method"]
     *
     * @param   string  $callback  The callback to be parsed
     *
     * @return array
     * @throws \LaborDigital\Typo3BetterApi\NamingConvention\NamingConventionException
     */
    public static function typoCallbackToArray(string $callback): array
    {
        // Concatinated, multi callbacks -> flexform
        if (stripos($callback, ';') !== false) {
            return array_map(
                [static::class, 'typoCallbackToArray'],
                array_filter(array_map('trim', explode(';', $callback)))
            );
        }
        $callbackParts = explode('->', $callback);
        if (count($callbackParts) !== 2) {
            throw new NamingConventionException(
                'Invalid TypoCallback given: "' . $callback . '". It has to be something like: namespace\\class->method'
            );
        }

        return [
            'class'  => trim(str_replace('/', '\\', $callbackParts[0]), '\\ '),
            'method' => trim($callbackParts[1], ' \\/()'),
        ];
    }
}
