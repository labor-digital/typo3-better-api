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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);
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
 * Last modified: 2020.08.23 at 23:23
 */


namespace LaborDigital\T3BA\FormEngine\Util;

use Neunerlei\Options\Options;

class FormEngineChangeFunctionBuilder
{
    
    /**
     * This method is basically the extracted renderer for an element's fieldChangeFunc
     * javascript counterpart. It can be used to extend all elements which don't support the onChange
     * configuration natively or any new element you want to enhance with that behaviour.
     *
     * @param   string  $ElementHtml      The rendered html of the element mostly $result['html'] (when extending
     *                                    exiting elements)
     * @param   array   $fieldChangeFunc  The array of field change javascript functions
     * @param   array   $options          An array of advanced configuration options:
     *                                    - prependElementHtml bool (true) True to prepend the element html to the
     *                                    output
     *                                    - pregPattern string This is optional to replace the internal preg pattern to
     *                                    extract the element's id from the source.
     *                                    - onlyForNewSections bool (FALSE): If this is set to true the event will only
     *                                    listen for elements in new flex form sections
     *
     * @return string The elementHtml with the attached javascript code / if $prependElementHtml is false only the js
     *                source is returned
     */
    public static function buildOnChangeFunction(
        string $ElementHtml,
        array $fieldChangeFunc,
        array $options = []
    ): string
    {
        // Prepare options
        $options = Options::make($options, [
            'prependElementHtml' => true,
            'pregPattern' => '/<(?:[^<]*?) id=["\']([^"\']*?)["\']/si',
            'onlyForNewSections' => false,
        ]);
        
        // Match the id in the given html
        preg_match($options['pregPattern'], $ElementHtml, $m);
        $id = $m[1];
        
        // Prepare the fieldchange func
        $src = ';' . str_replace(PHP_EOL, '', implode(';', $fieldChangeFunc)) . ';';
        
        // Prepare the default code
        $code = <<<JS
	var l = false;
	var nextE = document.querySelector("#{$id}:not(.initialized)");
	nextE.className += " initialized";
	var obs = new MutationObserver(function(m){
	  m.forEach(function(mu) {
	  	if(l) return;
	  	l = true;
	  	if(mu.type === 'attributes' && mu.attributeName === 'value' ||
	  		mu.type === 'childList' && nextE.nodeName === 'SELECT'){
	  	    setTimeout(function(){
                $src
	  	    }, 500);
	  	}
	  	setTimeout(function(){l = false}, 100);
	  });
	});
    obs.observe(nextE, {'attributes': true, 'childList': true});
    if(nextE.nodeName === 'SELECT'){
        nextE.onchange = function(){
            $src
        }
    }
JS;
        
        // Build special handling when sections are required
        if ($options['onlyForNewSections']) {
            $code = <<<JS
if(window.changeFuncInitialBindingsComplete){
	$code
}
JS;
        }
        
        // Append on change output
        return ($options['prependElementHtml'] ? $ElementHtml : '') . PHP_EOL . <<<HTML
<script type="text/javascript">
if(window.changeFuncInitialBindingsComplete !== true) window.changeFuncInitialBindingsComplete = false;
(function(){
	setTimeout(function(){
		window.changeFuncInitialBindingsComplete = true;
	}, 200);
$code
})();
</script>
HTML;
    }
}
