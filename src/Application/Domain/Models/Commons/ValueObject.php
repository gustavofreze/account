<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Commons;

use TinyBlocks\Vo\ValueObject as TinyBlocksValueObject;

/**
 * Domain type with value-based equality and no individual identity.
 */
interface ValueObject extends TinyBlocksValueObject
{
}
