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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Module;


use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\AbstractElementConfigurator;

class ModuleConfigurator extends AbstractElementConfigurator {
	
	/**
	 * One of file, help, system, tools, user or web.
	 * Defines the section of the left main menu where the module should be registered to.
	 * @var string
	 */
	protected $section = "web";
	
	/**
	 * Optional position of the module inside the selected section.
	 * The position is relative to another module key like before:key or after:key
	 * @var string|null
	 */
	protected $position;
	
	/**
	 * Optional path like EXT:extkey... that defines the translation file for this module. If this option is empty a
	 * translation file will automatically be created for you. You may also supply the name of a registered translation
	 * context. In that case the file of the context will be used
	 * @var string
	 */
	protected $translationFile;
	
	/**
	 * Defines which type of user can access the module
	 * @var array
	 */
	protected $access = ["user", "group"];
	
	/**
	 * Can be used to add additional options to the module definition. This can be useful if there are options that are
	 * not implemented by this interface.
	 * @var array
	 */
	protected $additionalOptions = [];
	
	/**
	 * The module key of this backend module
	 * @var string
	 */
	protected $moduleKey;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(string $pluginName, ExtConfigContext $context) {
		parent::__construct($pluginName, $context);
		
		$this->moduleKey = explode("_", $this->signature)[1];
		$this->translationFile = "LLL:EXT:" . $context->getExtKey() .
			"/Resources/Private/Language/locallang_mod_" . strtolower($pluginName) . ".xlf";
	}
	
	/**
	 * Returns the section of the left main menu where the module should be registered to.
	 * @return string
	 */
	public function getSection(): string {
		return $this->section;
	}
	
	/**
	 * Sets the section of the left main menu where the module should be registered to.
	 * One of file, help, system, tools, user or web.
	 *
	 * @param string $section
	 *
	 * @return ModuleConfigurator
	 */
	public function setSection(string $section): ModuleConfigurator {
		$this->section = $section;
		return $this;
	}
	
	/**
	 * Returns the position relative to another module key like before:key or after:key
	 * @return string|null
	 */
	public function getPosition(): ?string {
		return $this->position;
	}
	
	/**
	 * Sets the position relative to another module key like before:key or after:key
	 *
	 * @param string|null $position
	 *
	 * @return ModuleConfigurator
	 */
	public function setPosition(?string $position): ModuleConfigurator {
		$this->position = $position;
		return $this;
	}
	
	/**
	 * Returns the translation file for this module
	 * @return string
	 */
	public function getTranslationFile(): string {
		return $this->translationFile;
	}
	
	/**
	 * Sets the translation file for this module
	 *
	 * @param string $translationFile
	 *
	 * @return ModuleConfigurator
	 */
	public function setTranslationFile(string $translationFile): ModuleConfigurator {
		$this->translationFile = $translationFile;
		return $this;
	}
	
	/**
	 * Returns which type of user can access the module
	 * @return array
	 */
	public function getAccess(): array {
		return $this->access;
	}
	
	/**
	 * Sets which type of user can access the module
	 *
	 * @param array $access
	 *
	 * @return ModuleConfigurator
	 */
	public function setAccess(array $access): ModuleConfigurator {
		$this->access = $access;
		return $this;
	}
	
	/**
	 * Returns additional options to the module definition.
	 * @return array
	 */
	public function getAdditionalOptions(): array {
		return $this->additionalOptions;
	}
	
	/**
	 * Can be used to set additional options to the module definition.
	 *
	 * @param array $additionalOptions
	 *
	 * @return ModuleConfigurator
	 */
	public function setAdditionalOptions(array $additionalOptions): ModuleConfigurator {
		$this->additionalOptions = $additionalOptions;
		return $this;
	}
	
	/**
	 * Returns the module key of this backend module
	 * @return string
	 */
	public function getModuleKey(): string {
		return $this->moduleKey;
	}
}