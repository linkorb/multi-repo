<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Exception;

use UnderflowException;

class RepositoryHasUncommittedChangesException extends UnderflowException
{
}
