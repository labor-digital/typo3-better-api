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
 * Last modified: 2021.07.12 at 12:24
 */

define([
    'TYPO3/CMS/Backend/FormEngine/Container/InlineControlContainer',
    'jquery',
    'TYPO3/CMS/Backend/Modal',
    'TYPO3/CMS/Backend/Enum/Severity',
    'TYPO3/CMS/Backend/NewContentElementWizard',
    'TYPO3/CMS/Core/DocumentService'
], function (InlineControlContainer, j, modal, sev, wizard, docService) {
    
    return function (containerName, ajaxTarget) {
        var inline = new InlineControlContainer(containerName);
        
        docService.ready().then(function () {
            setTimeout(() => {
                j(inline.container).find('.t3js-toggle-new-content-element-wizard').each(function () {
                    var _this = $(this);
                    _this.removeClass('disabled');
                    _this.click(e => {
                        e.preventDefault();
                        var t = j(e.currentTarget);
                        
                        var d = modal.advanced({
                            callback: e => {
                                e.find('.t3js-modal-body').addClass('t3-new-content-element-wizard-window');
                            },
                            content: t.attr('href'),
                            severity: sev.SeverityEnum.notice,
                            size: modal.sizes.medium,
                            title: t.data('title'),
                            type: modal.types.ajax
                        });
                        
                        function onLoad()
                        {
                            var wz = new wizard.default(d);
                            wz.focusSearchField();
                            
                            var links = j(wz.context).find('.media a');
                            links.each(function (k, v) {
                                // Read params from the onClick attribute
                                var link = $(v);
                                var params = link.attr('onclick');
                                params = params.substring(params.indexOf('(') + 2);
                                params = params.substring(0, params.indexOf(')') - 1);
                                
                                // Remove the attribute and store the params for later use
                                link.attr('onclick', '');
                                link.data('link-params', JSON.stringify(decodeURIComponent(params)));
                            }).on('click', function (e) {
                                e.preventDefault();
                                if (!inline.isBelowMax()) {
                                    return;
                                }
                                
                                var recordUid = t.data('recordUid');
                                var objectId = inline.container.dataset.objectGroup;
                                if (recordUid) {
                                    objectId += '-' + recordUid;
                                }
                                
                                var sel = inline.container.querySelector('.t3js-create-new-selector');
                                inline.importRecord([
                                    objectId,
                                    sel ? sel.value : undefined,
                                    ajaxTarget,
                                    $(this).data('link-params')
                                ], recordUid || null);
                            });
                        }
                        
                        d.on('modal-loaded', function () {
                            d.on('shown.bs.modal', function () {onLoad();});
                        }).on('shown.bs.modal', function () {
                            d.on('modal-loaded', function () {onLoad();});
                        });
                    });
                });
            }, 50);
        });
    };
});