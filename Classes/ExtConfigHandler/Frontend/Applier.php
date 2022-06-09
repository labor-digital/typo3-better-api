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


namespace LaborDigital\T3ba\ExtConfigHandler\Frontend;


use LaborDigital\T3ba\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3ba\Event\Frontend\FrontendAssetFilterEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3ba\ExtConfigHandler\Common\Assets\AssetApplierTrait;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;

class Applier extends AbstractExtConfigApplier
{
    use AssetApplierTrait;
    use TypoContextAwareTrait;
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtLocalConfLoaded');
        $subscription->subscribe(FrontendAssetFilterEvent::class, 'onAssetFilter', ['priority' => -5000]);
    }
    
    /**
     * Registers the configured meta tag managers into the registry
     */
    public function onExtLocalConfLoaded(): void
    {
        $metaTagManagers = $this->state->get('t3ba.frontend.metaTagManagers');
        if (! empty($metaTagManagers)) {
            $registry = $this->getMetaTagManagerRegistry();
            foreach ($metaTagManagers as $args) {
                $registry->registerManager(...$args);
            }
        }
    }
    
    /**
     * Executes the asset collector configuration
     */
    public function onAssetFilter(FrontendAssetFilterEvent $event): void
    {
        $siteIdentifier = $this->getTypoContext()->site()->getCurrent()->getIdentifier();
        
        $this->applyAssetCollectorActions($siteIdentifier);
        $this->applyMetaTagActions($siteIdentifier);
        $this->applyHeaderAndFooterData($event, $siteIdentifier);
    }
    
    /**
     * Applies the registered raw html configuration to the page renderer
     *
     * @param   \LaborDigital\T3ba\Event\Frontend\FrontendAssetFilterEvent  $event
     * @param   string                                                      $siteIdentifier
     */
    protected function applyHeaderAndFooterData(FrontendAssetFilterEvent $event, string $siteIdentifier): void
    {
        $html = $this->state->get('typo.site.' . $siteIdentifier . '.frontend.html');
        if (! empty($html['header'])) {
            $event->getPageRenderer()->addHeaderData($html['header']);
        }
        if (! empty($html['footer'])) {
            $event->getPageRenderer()->addFooterData($html['footer']);
        }
    }
    
    /**
     * Injects the registered assets into the asset collector
     *
     * @param   string  $siteIdentifier
     */
    protected function applyAssetCollectorActions(string $siteIdentifier): void
    {
        $this->executeAssetCollectorActions(
            'typo.site.' . $siteIdentifier . '.frontend'
        );
    }
    
    /**
     * Injects the configured meta tags into the meta tag managers
     *
     * @param   string  $siteIdentifier
     */
    protected function applyMetaTagActions(string $siteIdentifier): void
    {
        $metaTagActions = $this->state->get('typo.site.' . $siteIdentifier . '.frontend.metaTagActions');
        if (! empty($metaTagActions)) {
            $registry = $this->getMetaTagManagerRegistry();
            foreach (['add' => 'addProperty', 'remove' => 'removeProperty'] as $listKey => $method) {
                if (! empty($metaTagActions[$listKey])) {
                    foreach ($metaTagActions[$listKey] as $property => $args) {
                        $registry->getManagerForProperty($property)->$method(...$args);
                    }
                }
            }
        }
    }
    
    /**
     * Internal helper to retrieve the meta tag manager registry.
     *
     * @return \TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry
     */
    protected function getMetaTagManagerRegistry(): MetaTagManagerRegistry
    {
        return $this->getTypoContext()->di()->makeInstance(MetaTagManagerRegistry::class);
    }
}