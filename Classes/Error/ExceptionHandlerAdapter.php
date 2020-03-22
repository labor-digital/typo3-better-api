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
 * Last modified: 2020.03.19 at 13:04
 */

namespace LaborDigital\Typo3BetterApi\Error;


use LaborDigital\Typo3BetterApi\BetterApiException;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Event\Events\ErrorFilterEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use Throwable;
use TYPO3\CMS\Core\Error\ExceptionHandlerInterface;
use TYPO3\CMS\Core\Error\ProductionExceptionHandler;

class ExceptionHandlerAdapter extends ProductionExceptionHandler implements ExceptionHandlerInterface {
	use CommonServiceLocatorTrait;
	
	/**
	 * The name of the registered exception handler
	 * @var string
	 */
	protected static $defaultExceptionHandler;
	
	/**
	 * The instance of the registered default exception handler
	 * @var ExceptionHandlerInterface
	 */
	protected $defaultExceptionHandlerInstance;
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		if (empty(static::$defaultExceptionHandler)) throw new BetterApiException("Could not create instance of: " . get_called_class() . " because no default exception handler was registered!");
		$this->defaultExceptionHandlerInstance = $this->getInstanceOf(static::$defaultExceptionHandler);
		
		// Disable the child exception handler's handling -> We will take care of that
		restore_exception_handler();
		
		// Register myself as real exception handler
		parent::__construct();
	}
	
	/**
	 * @inheritDoc
	 */
	public function handleException(Throwable $exception) {
		TypoEventBus::getInstance()->dispatch(($e = new ErrorFilterEvent($exception, NULL)));
		if ($e->getResult() !== NULL) return $e->getResult();
		return $this->defaultExceptionHandlerInstance->handleException($exception);
	}
	
	/**
	 * @inheritDoc
	 */
	public function echoExceptionWeb(Throwable $exception) {
		return $this->defaultExceptionHandlerInstance->handleException($exception);
	}
	
	/**
	 * @inheritDoc
	 */
	public function echoExceptionCLI(Throwable $exception) {
		return $this->defaultExceptionHandlerInstance->handleException($exception);
	}
	
	/**
	 * Internal helper to inject the default exception handler class
	 *
	 * @param string $defaultExceptionHandler
	 */
	public static function __setDefaultExceptionHandler(string $defaultExceptionHandler): void {
		static::$defaultExceptionHandler = $defaultExceptionHandler;
	}
}