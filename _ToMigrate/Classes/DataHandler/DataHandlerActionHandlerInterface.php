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
 * Last modified: 2020.03.21 at 20:48
 */

namespace LaborDigital\Typo3BetterApi\DataHandler;

interface DataHandlerActionHandlerInterface
{
    /**
     * This method can be used to traverse the TCA of a given table for a list of callable handlers.
     * It is in general agnostic to the type of handler it will execute. Use the $stackType argument in combination
     * with the static $stackTypeConfigKeyMap property to define which handler stack should be executed.
     *
     * The method is by default used to traverse the TCA of records for backend save filters, backend action handlers
     * and backend form filters. It is called in the BackendFormEventHandler class on multiple occasions in the
     * backend.
     *
     * It will also traverse flex forms that are defined on a table for possible handlers in it's definition and
     * add them to the execution stack.
     *
     * After it found all handlers and created a context configuration for each of them (a context configuration is a
     * lot of metadata for the next step to come)
     *
     * The context configuration's then will be converted into a context object which in turn is passed to each of the
     * registered handlers. All handler's can make changes to the linked value, based on the context (either the field,
     * or the table) they are bound to.
     *
     * @param   string  $stackType      The type of stack we should find the handlers for
     * @param   string  $tableName      The name of the table, which TCA we should traverse for handlers.
     * @param   string|int  $uid        The uid of the record we are executing the handlers for.
     * @param   object  $event          The typo3 event that lead to the call of this method.
     *                                  If you would ever want to create your own stack you should be able to pass
     *                                  a manually instantiated object here, as it is merely passed to the context
     *                                  object to provide additional information to the handlers
     * @param   array  $row             A database row, or a fraction of it, that corresponds with the given $uid.
     *                                  If this is empty, the method will try to request a fresh row from the database
     *                                  based on the given $uid. If any changes where made this reference will reflect
     *                                  all changes.
     * @param   bool  $isDirty          This reference is true if there were any changes made on the given $row
     */
    public function runActionStack(
        string $stackType,
        string $tableName,
        $uid,
        object $event,
        array &$row,
        &$isDirty = false
    );
}
