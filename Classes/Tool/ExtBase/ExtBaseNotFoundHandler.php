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
 * Last modified: 2021.06.27 at 14:38
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\ExtBase;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Rendering\TemplateRenderingService;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Controller\ErrorController;

class ExtBaseNotFoundHandler implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * This method is the internal implementation used in BetterActionController.
     * You should use the handleNotFound() method there instead of this.
     *
     * @param   string|null  $message
     * @param   array        $options
     * @param   \Closure     $redirector
     *
     * @return mixed|string
     * @throws \TYPO3\CMS\Core\Http\ImmediateResponseException
     * @see \LaborDigital\T3ba\ExtBase\Controller\BetterActionController::handleNotFound()
     * @internal
     */
    public function handle(?string $message, array $options, \Closure $redirector)
    {
        $options = $this->validateOptions($options);
        $message = $message ?? '404 - Something was not found';
        
        if ($options['redirectToPid']) {
            return $redirector($this->cs()->links->getLink()->withPid($options['redirectToPid'])->build());
        }
        
        if ($options['redirectToLink']) {
            return $redirector($this->cs()->links->getLink($options['redirectToLink'])->build());
        }
        
        if ($options['renderTemplate']) {
            HttpUtility::setResponseCode(404);
            $tsfeService = $this->cs()->tsfe;
            if ($tsfeService->hasTsfe()) {
                $tsfeService->getTsfe()->set_no_cache($message);
            }
            
            return $this->getService(TemplateRenderingService::class)
                        ->getFluidView($options['renderTemplate'])
                        ->assign('errorMessage', $message)
                        ->render();
        }
        
        /** @noinspection NullPointerExceptionInspection */
        $response = $this->makeInstance(ErrorController::class)->pageNotFoundAction(
            $this->cs()->typoContext->request()->getRootRequest(), $message);
        $response = $response->withStatus(404);
        throw new ImmediateResponseException($response);
    }
    
    /**
     * Validates the given options array
     *
     * @param   array  $options
     *
     * @return array
     */
    protected function validateOptions(array $options): array
    {
        return Options::make($options, [
            'redirectToPid' => [
                'type' => ['null', 'string', 'int'],
                'default' => null,
            ],
            'redirectToLink' => [
                'type' => ['null', 'string'],
                'default' => null,
            ],
            'renderTemplate' => [
                'type' => ['null', 'string'],
                'default' => null,
            ],
        ]);
    }
}