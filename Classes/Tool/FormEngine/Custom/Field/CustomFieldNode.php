<?php
declare(strict_types=1);
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
 * Last modified: 2020.03.19 at 02:59
 */

namespace LaborDigital\T3BA\Tool\FormEngine\Custom\Field;

use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Event\FormEngine\CustomFieldPostProcessorEvent;
use LaborDigital\T3BA\Tool\FormEngine\Custom\CustomFormException;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\MathUtility;

class CustomFieldNode extends AbstractFormElement
{
    use ContainerAwareTrait;

    /**
     * Holds the current context to be able to update our local data if required
     *
     * @var \LaborDigital\T3BA\Tool\FormEngine\Custom\Field\CustomFieldContext
     */
    protected $context;

    /**
     * @inheritDoc
     */
    public function render()
    {
        // Create a new context
        $this->context = $this->makeInstance(
            CustomFieldContext::class,
            [
                [
                    'rawData'           => $this->data,
                    'iconFactory'       => $this->iconFactory,
                    'rootNode'          => $this,
                    'defaultInputWidth' => $this->defaultInputWidth,
                    'maxInputWidth'     => $this->maxInputWidth,
                    'minInputWidth'     => $this->minimumInputWidth,
                ],
            ]
        );

        // Validate if the class exists
        $className = $this->context->getClassName();
        if (! class_exists($className)) {
            throw new CustomFormException(
                'Could not render your field: ' . $this->context->getFieldName()
                . ' to use the custom field class: ' . $className
                . '. Because the class does not exist!');
        }

        $i = $this->getContainer()->has($className) ?
            $this->getService($className) : $this->makeInstance($className);

        if (! $i instanceof CustomFieldInterface) {
            throw new CustomFormException(
                'Could not render your field: ' . $this->context->getFieldName()
                . " to use the custom field with class: $className. Because the class does not implement the required "
                . CustomFieldInterface::class . ' interface!');
        }

        $i->setContext($this->context);

        // Initialize the result
        $result         = $this->initializeResultArray();
        $result['html'] = $i->render();

        // Update the data of this node
        $this->refreshData();

        // Check if we can render the field wizard
        $fieldWizardResult = ['html' => ''];
        if (method_exists($this, 'renderFieldWizard')) {
            $fieldWizardResult = $this->renderFieldWizard();
            $result            = $this->mergeChildReturnIntoExistingResult($result, $fieldWizardResult, false);
        }

        // Check if we should apply the outer wrap
        if ($this->context->isApplyOuterWrap()) {
            $config = $this->context->getConfig()['config'] ?? [];

            // Calculate field size
            $size           = Arrays::getPath($config, ['size'], $this->context->getDefaultInputWidth());
            $size           = MathUtility::forceIntegerInRange($size, $this->context->getMinInputWidth(),
                $this->context->getMaxInputWidth());
            $width          = (int)$this->context->getRootNode()->callMethod('formMaxWidth', [$size]);
            $html           = $result['html'];
            $wizardHtml     = $fieldWizardResult['html'];
            $result['html'] = <<<HTML
				<div class="form-control-wrap" style="max-width: $width px;">
					<div class="form-wizards-wrap">
						<div class="form-wizards-element">
							$html
						</div>
						<div class="form-wizards-items-bottom">
							$wizardHtml
						</div>
					</div>
				</div>
HTML;
        }

        // Allow the outside world to filter the result
        return $this->cs()->eventBus->dispatch(new CustomFieldPostProcessorEvent(
            $this->context,
            $i->filterResultArray($result)
        ))->getResult();
    }

    /**
     * Internal helper to allow the external world access to all protected methods of this object
     *
     * @param   string  $method
     * @param   array   $args
     *
     * @return mixed
     * @throws \LaborDigital\T3BA\Tool\FormEngine\Custom\CustomFormException
     */
    public function callMethod(string $method, array $args = [])
    {
        // Check if the method exists
        if (! method_exists($this, $method)) {
            throw new CustomFormException(
                'It is not allowed to call method: ' . $method
                . ' on the root node, because the method does not exist!');
        }

        // Make sure our data is up to data
        $this->refreshData();

        // Call the method
        return call_user_func_array([$this, $method], $args);
    }

    /**
     * The companion to __callMethod. Checks if a certain, protected method exists or not
     *
     * @param   string  $method
     *
     * @return bool
     */
    public function hasMethod(string $method): bool
    {
        return method_exists($this, $method);
    }

    /**
     * Internal helper to synchronize the context's data back to this nodes data storage
     */
    public function refreshData(): void
    {
        $this->data = $this->context->getRawData();
    }
}
