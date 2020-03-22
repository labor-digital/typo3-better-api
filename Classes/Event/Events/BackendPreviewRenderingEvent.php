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
 * Last modified: 2020.03.19 at 20:08
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;


use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\BackendPreviewRenderingEventAdapter;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use TYPO3\CMS\Backend\View\PageLayoutView;

/**
 * Class BackendPreviewRenderingEvent
 *
 * Is called when the backend tries to draw a preview for a single content element.
 * Mostly for use in the backend preview service
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 * @see     \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewService
 */
class BackendPreviewRenderingEvent implements CoreHookEventInterface {
	/**
	 * The row of the tt_content record that should be rendered as backend preview
	 * @var array
	 */
	protected $row;
	
	/**
	 * The header set for the backend preview
	 * @var string
	 */
	protected $header;
	
	/**
	 * The rendered content for the record
	 * @var string
	 */
	protected $content;
	
	/**
	 * True as long as the record was not manually rendered
	 * @var bool
	 */
	protected $drawItem;
	
	/**
	 * The layout view to render the element for
	 * @var \TYPO3\CMS\Backend\View\PageLayoutView
	 */
	protected $view;
	
	/**
	 * @inheritDoc
	 */
	public static function getAdapterClass(): string {
		return BackendPreviewRenderingEventAdapter::class;
	}
	
	/**
	 * BackendPreviewRenderingEvent constructor.
	 *
	 * @param array                                  $row
	 * @param string                                 $header
	 * @param string                                 $content
	 * @param bool                                   $drawItem
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $view
	 */
	public function __construct(array $row, string $header, string $content, bool $drawItem, PageLayoutView $view) {
		$this->row = $row;
		$this->header = $header;
		$this->content = $content;
		$this->drawItem = $drawItem;
		$this->view = $view;
	}
	
	/**
	 * Returns true if the element was rendered, false if it is not yet rendered
	 * @return bool
	 */
	public function isRendered(): bool {
		return !$this->drawItem;
	}
	
	/**
	 * Sets the element to "has been rendered" meaning there are no other actions required
	 */
	public function setAsRendered(): void {
		$this->drawItem = FALSE;
	}
	
	/**
	 * Returns the row of the tt_content record that should be rendered as backend preview
	 * @return array
	 */
	public function getRow(): array {
		return $this->row;
	}
	
	
	/**
	 * Returns the header set for the backend preview
	 * @return string
	 */
	public function getHeader(): string {
		return $this->header;
	}
	
	/**
	 * Updates the header set for the backend preview
	 *
	 * @param string $header
	 *
	 * @return BackendPreviewRenderingEvent
	 */
	public function setHeader(string $header): BackendPreviewRenderingEvent {
		$this->header = $header;
		return $this;
	}
	
	/**
	 * Returns the rendered content for the record
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}
	
	/**
	 * Updates the rendered content for the record
	 *
	 * @param string $content
	 *
	 * @return BackendPreviewRenderingEvent
	 */
	public function setContent(string $content): BackendPreviewRenderingEvent {
		$this->content = $content;
		return $this;
	}
	
	/**
	 * Returns the layout view to render the element for
	 * @return \TYPO3\CMS\Backend\View\PageLayoutView
	 */
	public function getView(): PageLayoutView {
		return $this->view;
	}
}