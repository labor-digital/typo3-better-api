<?php
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
 * Last modified: 2020.08.23 at 12:08
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use Doctrine\DBAL\Driver\Mysqli\MysqliConnection;
use Doctrine\DBAL\Driver\Mysqli\MysqliStatement;
use Kint\Kint;
use Kint\Parser\BlacklistPlugin;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\Typo3BetterApi\Kint\LazyLoadingPlugin;
use LaborDigital\Typo3BetterApi\Kint\TypoInstanceTypePlugin;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Throwable;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class DbgConfigurationStage implements BootStageInterface
{
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        if (! function_exists('dbgConfig') || ! defined('_DBG_CONFIG_LOADED')) {
            return;
        }

        // Register our Plugins
        Kint::$plugins[] = LazyLoadingPlugin::class;
        Kint::$plugins[] = TypoInstanceTypePlugin::class;

        // Register pre hook to fix broken typo3 iframe
        $recursion = false;
        dbgConfig('postHooks', static function () use (&$recursion) {
            if ($recursion) {
                return;
            }
            $recursion = true;
            try {
                if ((defined('TYPO3_MODE') && TYPO3_MODE === 'BE') && PHP_SAPI !== 'cli') {
                    if (Kint::$mode_default !== Kint::MODE_RICH) {
                        return;
                    }
                    flush();
                    echo <<<HTML
							<script type="text/javascript">
							setTimeout(function () {
								document.getElementsByTagName("html")[0].setAttribute("style", "height:100vh; overflow:auto");
								document.getElementsByTagName("body")[0].setAttribute("style", "height:100vh; overflow:auto");
								}, 50);
							</script>
HTML;
                    flush();
                }
            } catch (Throwable $e) {
                // Ignore this...
            }
            $recursion = false;
        });

        // Register blacklisted objects to prevent kint from breaking apart ...
        if (class_exists(BlacklistPlugin::class)) {
            BlacklistPlugin::$shallow_blacklist[] = ReflectionService::class;
            BlacklistPlugin::$shallow_blacklist[] = ObjectManager::class;
            BlacklistPlugin::$shallow_blacklist[] = DataMapper::class;
            BlacklistPlugin::$shallow_blacklist[] = PersistenceManager::class;
            BlacklistPlugin::$shallow_blacklist[] = QueryObjectModelFactory::class;
            BlacklistPlugin::$shallow_blacklist[] = ContentObjectRenderer::class;
            BlacklistPlugin::$shallow_blacklist[] = TypoEventBus::class;
            BlacklistPlugin::$shallow_blacklist[] = QueryResult::class;
            BlacklistPlugin::$shallow_blacklist[] = MysqliConnection::class;
            BlacklistPlugin::$shallow_blacklist[] = MysqliStatement::class;
            BlacklistPlugin::$shallow_blacklist[] = ContainerInterface::class;
            BlacklistPlugin::$shallow_blacklist[] = ListenerProviderInterface::class;
        }
    }

}
