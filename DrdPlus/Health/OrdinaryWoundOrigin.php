<?php
namespace DrdPlus\Health;

use Granam\Tools\ValueDescriber;

/**
 * @method static OrdinaryWoundOrigin getEnum($value)
 */
class OrdinaryWoundOrigin extends WoundOrigin
{
    const ORDINARY = 'ordinary';

    /**
     * @return OrdinaryWoundOrigin
     */
    public static function getIt(): OrdinaryWoundOrigin
    {
        return static::getEnum(self::ORDINARY);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Exceptions\UnknownWoundOriginCode
     * @throws \Doctrineum\String\Exceptions\UnexpectedValueToEnum
     */
    protected static function convertToEnumFinalValue($enumValue): string
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if ($enumFinalValue !== self::ORDINARY) {
            throw new Exceptions\UnknownWoundOriginCode(
                'Got unexpected code of wound origin ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

    /**
     * @return bool
     */
    public function isSeriousWoundOrigin(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isOrdinaryWoundOrigin(): bool
    {
        return true;
    }
}