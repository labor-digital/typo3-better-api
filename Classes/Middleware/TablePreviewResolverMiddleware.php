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
 * Last modified: 2021.07.13 at 18:34
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Middleware;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\Tca\Preview\PreviewHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Frontend\Middleware\BackendUserAuthenticator;

class TablePreviewResolverMiddleware extends BackendUserAuthenticator
{
    public const PREVIEW_QUERY_KEY = 't3ba-table-preview';
    
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($request->getQueryParams()[static::PREVIEW_QUERY_KEY])) {
            // To create the simulation, we need to forcefully create a backend user authentication before its normal lifecycle
            $this->initializeBackendUser($request);
            $request = $this->getService(PreviewHandler::class)->handleRequest($request);
        }
        
        return $handler->handle($request);
    }
}
