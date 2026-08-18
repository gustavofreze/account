<?php

declare(strict_types=1);

namespace Account\Application\Domain\Models\Commons;

use TinyBlocks\Math\BigDecimal;
use TinyBlocks\Math\RoundingMode;

final readonly class Decimal implements ValueObject
{
    use ValueObjectBehavior;

    private function __construct(private BigDecimal $number)
    {
    }

    public static function of(int $scale, float $value): Decimal
    {
        $number = BigDecimal::fromFloat(value: $value);

        return new Decimal(number: $number->toScale(scale: $scale, rounding: RoundingMode::HalfUp));
    }

    public function absolute(): Decimal
    {
        return new Decimal(number: $this->number->absolute());
    }

    public function subtract(Decimal $subtrahend): Decimal
    {
        return new Decimal(number: $this->number->minus(subtrahend: $subtrahend->number));
    }

    public function isNegative(): bool
    {
        return $this->number->isNegative();
    }

    public function toFloat(): float
    {
        return $this->number->toFloat();
    }
}
