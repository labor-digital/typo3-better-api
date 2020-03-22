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

namespace LaborDigital\Typo3BetterApi\Translation\FileSync;


class TranslationMapping {
	
	/**
	 * The base file to use as origin for the other languages
	 * @var \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFile
	 */
	protected $baseFile;
	
	/**
	 * The list of languages except the base file
	 * @var \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFile[]
	 */
	protected $translations = [];
	
	/**
	 * A list that maps the messages over all registered languages by their id
	 * @var array
	 */
	protected $map = [];
	
	/**
	 * A list that maps the source text to the map id, as fallback if the id's were changed
	 * in the origin file, but not in the children
	 * @var array
	 */
	protected $sourceMap = [];
	
	/**
	 * Sets the base file which is used as origin to sync all other registered translation files with
	 *
	 * @param \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFile $baseFile
	 */
	public function setBaseFile(TranslationFile $baseFile) {
		$this->baseFile = $baseFile;
		foreach ($baseFile->messages as $message) {
			$sourceId = md5(trim($message->source));
			$this->map[$message->id] = [
				0 => $message,
			];
			$this->sourceMap[$sourceId][] = $message->id;
		}
	}
	
	/**
	 * Returns the current base / origin file
	 * @return \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFile
	 */
	public function getBaseFile(): TranslationFile {
		return $this->baseFile;
	}
	
	/**
	 * Adds additional translation files to the mapping which will be synchronized with the registered origin file
	 *
	 * @param \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFile $translationFile
	 *
	 * @throws \LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationFileSyncException
	 */
	public function addTranslationFile(TranslationFile $translationFile) {
		
		// Check if we got an origin file
		if (!isset($this->baseFile))
			throw new TranslationFileSyncException("You may not register translation files before you registered the base file!");
		
		// Read the content into the map
		$fileId = spl_object_id($translationFile);
		$this->translations[$fileId] = $translationFile;
		foreach ($translationFile->messages as $message) {
			$sourceId = md5(trim($message->source));
			
			// Try to map the id over the source string (Fallback if source id was changed)
			if (!isset($this->map[$message->id])) {
				if (isset($this->sourceMap[$sourceId])) {
					// Go the fast route if there is only a single match
					if (count($this->sourceMap[$sourceId]) === 1) {
						$message->id = $this->sourceMap[$sourceId][0];
					} else {
						// Try to figure out the correct match
						$matches = array_filter($this->sourceMap[$sourceId], function ($v) use ($translationFile) {
							return !isset($translationFile->messages[$v]);
						});
						
						// Mapping failed
						if (empty($matches)) continue;
						
						// Update id
						$message->id = reset($matches);
					}
					
				} else continue;
			}
			$this->map[$message->id][$fileId] = $message;
		}
	}
	
	/**
	 * Returns the list of all registered translation files (except the base file)
	 * @return array
	 */
	public function getTranslationFiles(): array {
		return $this->translations;
	}
	
	/**
	 * The main method which synchronizes the file contents with each other,
	 * updating id's and sources to match the origin file
	 */
	public function synchronize() {
		
		// Sort the map
		ksort($this->map);
		
		// Loop over all languages
		foreach ($this->translations as $fileId => $lang) {
			// Update the meta data
			$lang->productName = $this->baseFile->productName;
			$lang->sourceLang = $this->baseFile->sourceLang;
			
			// Reset the internal message storage
			$lang->messages = [];
			
			// Rebuild the messages list based on the mapping
			foreach ($this->map as $id => $items) {
				
				// Check if we know this message in the target lang
				if (!isset($items[$fileId])) {
					// Clone the message from the source message
					$message = clone $items[0];
					if (!$message->isNote)
						$message->target = "COPY FROM: " . $this->baseFile->sourceLang . " - " . $message->source;
				} else {
					// Update the id and the source
					$message = $items[$fileId];
					$message->id = $id;
					if ($message->isNote) {
						// Update note if required
						if (empty($message->note)) $message->note = $items[0]->note;
					} else {
						$message->source = $items[0]->source;
					}
				}
				$lang->messages[$id] = $message;
			}
		}
		
		// Flush storage
		$this->map = [];
		$this->sourceMap = [];
	}
}