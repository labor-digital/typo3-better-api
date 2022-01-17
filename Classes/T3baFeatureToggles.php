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
 * Last modified: 2021.07.19 at 13:23
 */

declare(strict_types=1);


namespace LaborDigital\T3ba;


interface T3baFeatureToggles
{
    /**
     * V11 will introduce a new way of generating MM TCA tables. An update wizard is provided.
     * This toggle, will activate the feature in v10 so you don't need to update in the future
     * NOTE: Toggle will be removed in v12
     */
    public const TCA_V11_MM_TABLES = 't3ba.TCA.MM.V11Definition';
    
    /**
     * V11 will generate inline relations through an MM table instead of specific fields on the foreign table.
     * An update wizard is provided. This toggle, will activate the feature in v10 so you don't need to update in the future
     *
     * NOTE: Toggle will be removed in v12
     */
    public const TCA_V11_INLINE_RELATIONS = 't3ba.TCA.Inline.V11Definition';
    
    /**
     * Per v10 implementation, content type tables, began with ct_ followed by the type name.
     * After getting sensible feedback, v11 will instead begin the table with tt_content_ followed by the type name.
     * Also the ct_child field in tt_content will be renamed to t3ba_ct_child to match the naming schema.
     * This toggle, will activate the feature in v10 so you don't need to update in the future
     *
     * NOTE: Toggle will be removed in v12
     */
    public const CONTENT_TYPE_V11_NAMING_SCHEMA = 't3ba.ContentType.V11Naming';
    
    /**
     * V11 will strip duplicate namespaces from a class name when the table name is derived.
     * So a class like: Vendor/ExtKey/Configuration/Table/Foo/FooTable generates:
     * tx_extkey_domain_model_foo instead of tx_extkey_domain_model_foo_foo
     * Similarly a class like Vendor/ExtKey/Configuration/Table/Foo/FooBarTable will
     * then generate: tx_extkey_domain_model_foo_bar which makes more sense in general.
     *
     * NOTE: Toggle will be removed in v12
     */
    public const TCA_V11_NESTED_TABLE_NAMES = 't3ba.TCA.TableName.V11Naming';
    
    /**
     * Beginning in V11 the site-based ext-config options will be loaded along-side the normal ext-config
     * data in the Main loader. This will replace the old load strategy AFTER the normal ext-config state
     * has been loaded. Enabling this feature toggle, will use the same behaviour in v10.
     *
     * NOTE: Toggle will be removed in v11
     */
    public const EXT_CONFIG_V11_SITE_BASED_CONFIG = 't3ba.ExtConfig.V11SiteBasedLoadOrder';
    
    /**
     * Beginning with V11 the name of dynamic TCA tables will be changed to be generically used
     * for multiple types. This will avoid a huge number of palettes with the same content.
     *
     * NOTE: Toggle will be removed in v11
     */
    public const TCA_V11_REUSE_DYNAMIC_PALETTES = 't3ba.TCA.Dumper.V11ReuseDynamicPalettes';
}