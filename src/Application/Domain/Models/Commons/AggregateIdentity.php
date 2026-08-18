<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Commons;

use TinyBlocks\BuildingBlocks\Entity\Identity as TinyBlocksIdentity;

/**
 * Identity of an aggregate root within the account domain.
 */
interface AggregateIdentity extends TinyBlocksIdentity
{
}
