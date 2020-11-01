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
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\PathUtil\Path;
use ReflectionMethod;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Repository;

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
     * @param   string  $extKey
     *
     * @return string
     */
    public static function vendorFromExtKey(string $extKey): string
    {
        if (strpos($extKey, '.') === false) {
            return '';
        }

        return substr($extKey, 0, strpos($extKey, '.'));
    }


    /**
     * Tries to find the correct sql database table for the given selector.
     *
     * IMPORTANT: This method requires a TypoScript as well as the TCA to be set up!
     *
     * @param   string|AbstractEntity|Repository  $selector  The selector to find the database table for:
     *                                                       The valid options are:
     *                                                       - a string already containing a table name -> will be
     *                                                       kept as is
     *                                                       - the class|instance of a domain model
     *                                                       - the class|instance of a domain repository
     *
     * @return string
     */
    public static function resolveTableName($selector): string
    {
        if (is_object($selector)) {
            if ($selector instanceof BetterRepository) {
                return $selector->getTableName();
            }
            if ($selector instanceof Repository || $selector instanceof AbstractEntity) {
                $selector = get_class($selector);
            }
        }

        if (is_string($selector)) {
            if (class_exists($selector)) {
                // Resolve repository class
                if (in_array(Repository::class, class_parents($selector), true)) {
                    $selector = ClassNamingUtility::translateRepositoryNameToModelName($selector);
                }

                // Resolve entity class
                if (in_array(AbstractEntity::class, class_parents($selector), true)) {
                    return TypoContext::getInstance()->Di()->getInstanceOf(DataMapper::class)
                                      ->getDataMap($selector)->getTableName();
                }
            }

            return $selector;
        }

        throw new InvalidArgumentException('Could not convert the given selector: ' . $selector
                                           . ' into a database table name!');
    }

    /**
     * Resolves the given callable into an actual callable.
     *
     * @param   string|array|callable  $callable               Allowed values are:
     *                                                         - callable types -> will be passed through
     *                                                         - TYPO3 callables like namespace\\class->method
     *                                                         - instantiatable callables [Class::class, 'method']
     *                                                         Class will be instantiated using the container if
     *                                                         "method" is not declared "static".
     * @param   bool                   $instantiateIfRequired  Set this to false if you don't want to instantiate the
     *                                                         class of instantiable callbacks. Mostly for legacy
     *                                                         support.
     *
     * @return callable
     */
    public static function resolveCallable($callable, bool $instantiateIfRequired = true): callable
    {
        if (is_string($callable)) {
            // Return something that is already callable
            if (is_callable($callable)) {
                return $callable;
            }

            // Resolve a list of multiple callables -> FlexForms
            if (strpos($callable, ';') !== false) {
                return array_map(
                    [static::class, __FUNCTION__],
                    array_filter(array_map('trim', explode(';', $callable)))
                );
            }

            // Resolve typo callable
            if (strpos($callable, '->') !== false) {
                $parts = explode('->', $callable);
                if (count($parts) !== 2) {
                    throw new InvalidArgumentException(
                        'Invalid callback given: "' . $callable
                        . '". It has to be something like: namespace\\class->method'
                    );
                }

                $callable = [
                    trim(str_replace('/', '\\', $parts[0]), '\\ '),
                    trim($parts[1], ' \\/()'),
                ];
            }
        }

        if (is_array($callable) && count($callable) === 2) {
            // Already a callable using an instance, or instantiation disabled
            if (is_callable($callable) && (is_object($callable[0]) || ! $instantiateIfRequired)) {
                return $callable;
            }

            // Check if we have to instantiate the class first
            if (! (new ReflectionMethod($callable[0], $callable[1]))->isStatic()) {
                return [
                    TypoContext::getInstance()->Di()->getInstanceOf($callable[0]),
                    $callable[1],
                ];
            }
        }

        if (! is_callable($callable)) {
            throw new InvalidArgumentException('Could not resolve the given callable into an actual callable!');
        }

        return $callable;
    }
}
