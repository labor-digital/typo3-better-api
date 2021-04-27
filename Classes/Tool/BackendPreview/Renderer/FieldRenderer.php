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
 * Last modified: 2021.04.27 at 19:35
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\BackendPreview\Renderer;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Tool\Fal\FalService;
use LaborDigital\T3BA\Tool\Translation\Translator;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class FieldRenderer implements PublicServiceInterface
{
    use ContainerAwareTrait;

    /**
     * @var \LaborDigital\T3BA\Tool\Translation\Translator
     */
    protected $translator;

    /**
     * @var \LaborDigital\T3BA\Tool\Fal\FalService
     */
    protected $falService;

    public function __construct(Translator $translator, FalService $falService)
    {
        $this->translator = $translator;
        $this->falService = $falService;
    }

    public function renderLabel(string $tableName, string $fieldName): string
    {
        $fieldTca = $GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? [];

        // @todo switch this to translateBe when it was implemented
        if (isset($fieldTca['label'])) {
            return $this->translator->translate($fieldTca['label']);
        }

        return Inflector::toHuman($fieldName);
    }

    public function render(string $tableName, string $fieldName, array $row, bool $textOnly = false): ?string
    {
        $fieldTca = $GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? [];

        if (empty($fieldTca) || empty($row[$fieldName])) {
            return null;
        }

        if (isset($row['uid']) && ($fieldTca['config']['foreign_table'] ?? null) === 'sys_file_reference') {
            return $textOnly
                ? $this->renderFileFieldText($tableName, $fieldTca, $fieldName, $row)
                : $this->renderFileField($tableName, $fieldTca, $fieldName, $row);
        }

        if (($fieldTca['config']['renderType'] ?? null) === 'inputLink') {
            return $this->renderLinkField((string)$row[$fieldName]);
        }

        $content = $this->htmlEncode(BackendUtility::getProcessedValue($tableName, $fieldName, $row[$fieldName]));

        if (empty($content)) {
            $content = $this->htmlEncode($row[$fieldName]);
        }

        return $content;
    }


    /**
     * Renders a single file field with with a preview if an image is referenced
     *
     * @param   string  $tableName
     * @param   array   $fieldTca
     * @param   string  $field
     * @param   array   $row
     *
     * @return string
     */
    protected function renderFileField(string $tableName, array $fieldTca, string $field, array $row): string
    {
        $matchField = $fieldTca['config']['foreign_match_fields']['fieldname'] ?? $field;
        $files      = $this->falService->getFile($row['uid'], $tableName, $matchField, false);
        $maxItems   = $fieldTca['config']['maxItems'] ?? 1;

        $content = [];
        foreach ($files as $c => $file) {
            if ($c === $maxItems) {
                break;
            }

            $info = $this->falService->getFileInfo($file);
            if ($info->isImage()) {
                $content[] = '<img src="' .
                             $this->htmlEncode($this->falService->getResizedImageUrl($file, ['maxWidth' => 200])) .
                             '" style="width:100%; max-width:200px"/>';
            } else {
                $content[] = $this->htmlEncode($info->getFileName());
            }
        }

        if (empty($content)) {
            return '&nbsp;';
        }

        if (count($content) === 1) {
            return reset($content);
        }

        return '<ul><li>' . implode('</li><li>', $content) . '</li></ul>';
    }

    /**
     * Renders a single file field as text only output
     *
     * @param   string  $tableName
     * @param   array   $fieldTca
     * @param   string  $field
     * @param   array   $row
     *
     * @return string
     */
    protected function renderFileFieldText(string $tableName, array $fieldTca, string $field, array $row): string
    {
        $matchField = $fieldTca['config']['foreign_match_fields']['fieldname'] ?? $field;
        $files      = $this->falService->getFile($row['uid'], $tableName, $matchField, false);
        $maxItems   = $fieldTca['config']['maxItems'] ?? 1;


        $content = [];
        foreach ($files as $c => $file) {
            if ($c === $maxItems) {
                break;
            }

            $content[] = $file->getNameWithoutExtension() . ' [' . $file->getUid() . ']';
        }

        return implode(',', $content);
    }

    /**
     * Renders a typo link value as somewhat readable link
     *
     * @param   string  $value
     *
     * @return string
     */
    protected function renderLinkField(string $value): string
    {
        try {
            $linkData = $this->makeInstance(LinkService::class)->resolve(
                $this->makeInstance(TypoLinkCodecService::class)->decode($value)['url'] ?? ''
            );
        } catch (\Throwable $exception) {
            return $value;
        }

        if (empty($linkData['type'])) {
            return $value;
        }

        switch ($linkData['type']) {
            case LinkService::TYPE_PAGE:
                $record = BackendUtility::readPageAccess($linkData['pageuid'], '1=1');
                if (! empty($record['uid'])) {
                    return $record['_thePathFull'] . '[' . $record['uid'] . ']';
                }

                return $value;
            case LinkService::TYPE_EMAIL:
                return $linkData['email'] ?? $value;
            case LinkService::TYPE_URL:
                return $linkData['url'] ?? $value;
            case LinkService::TYPE_FILE:
                if (! empty($linkData['file'])) {
                    $linkData['file']->getNameWithoutExtension() . ' [' . $linkData['file']->getUid() . ']';
                }

                return $value;
            case LinkService::TYPE_FOLDER:
                if (! empty($linkData['folder'])) {
                    $linkData['folder']->getPublicUrl();
                }

                return $value;
            case LinkService::TYPE_RECORD:
//                $this->getService(TypoScriptService::class)->getTsConfig()
                dbge($linkData);
                break;
            case LinkService::TYPE_TELEPHONE:
                return $linkData['telephone'] ?? $value;

        }

        return $value;
    }

    /**
     * Helper to encode html special characters
     *
     * @param $value
     *
     * @return string
     */
    protected function htmlEncode($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5);
    }
}
