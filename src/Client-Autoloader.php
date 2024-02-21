<?php
/*
 * Copyright 2024 NetKnights GmbH - lukas.matusiewicz@netknights.it
 * <p>
 * Licensed under the GNU AFFERO GENERAL PUBLIC LICENSE Version 3;
 * you may not use this file except in compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

spl_autoload_register('autoLoader');

/**
 * This autoloader includes the privacyIDEA-PHP-Client files to the project.
 */
function autoLoader($className): bool
{
    $fullPath = dirname(__FILE__) . "/" . $className . ".php";
    if (file_exists($fullPath))
    {
        require_once $fullPath;
        return true;
    }
    else
    {
        return false;
    }
}