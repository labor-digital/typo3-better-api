<?php
/*
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
 * Last modified: 2020.10.18 at 20:04
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\DataHook;


interface DataHookTypes
{
    /**
     * The key in the TCA of a table or a single field we use to resolve the hooks
     */
    public const TCA_DATA_HOOK_KEY = '@DATA_HOOK';

    /**
     * Executed when the backend builds the form for a record using the form engine
     */
    public const TYPE_FORM = 'form';

    /**
     * Executed when a record is saved.
     * This is executed before the data handler validates the data
     */
    public const TYPE_SAVE = 'save';

    /**
     * Similar to SAVE, but is executed, after the data handler validated the data.
     * Note, that the data ONLY contains the list of changed fields!
     */
    public const TYPE_SAVE_LATE = 'save.late';

    /**
     * Similar to SAVE and SAVE_LATE, but is executed after the data was stored in the database.
     * This allows last minute processing of newly created records
     */
    public const TYPE_SAVE_AFTER_DB = 'save.afterDb';

    /**
     * Executed when a record is copied
     */
    public const TYPE_COPY = 'copy';

    /**
     * Executed when a record is moved to another page
     */
    public const TYPE_MOVE = 'move';

    /**
     * Executed when a record is marked as deleted
     */
    public const TYPE_DELETE = 'delete';

    /**
     * Executed when a previously deleted record is restored from the recycle bin
     */
    public const TYPE_RESTORE = 'undelete';

    public const TYPE_LOCALIZE             = 'localize';
    public const TYPE_COPY_TO_LANGUAGE     = 'copyToLanguage';
    public const TYPE_INLINE_LOCALIZE_SYNC = 'inlineLocalizeSynchronize';
    public const TYPE_VERSION              = 'version';
}
