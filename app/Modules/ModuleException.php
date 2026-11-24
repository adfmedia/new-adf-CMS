<?php

namespace App\Modules;

use RuntimeException;

/**
 * A module action that can't be done, with a message safe to show the user
 */
class ModuleException extends RuntimeException
{
}
