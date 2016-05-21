<?php
namespace DrdPlus\Person\Health;

use Granam\Tools\ValueDescriber;

class OrdinaryWoundOrigin extends WoundOrigin
{
    const ORDINARY = 'ordinary';

    /**
     * @return OrdinaryWoundOrigin
     */
    public static function getIt()
    {
        return static::getEnum(self::ORDINARY);
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Exceptions\UnknownWoundOriginCode
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if ($enumFinalValue !== self::ORDINARY) {
            throw new Exceptions\UnknownWoundOriginCode(
                'Got unexpected code of wound origin ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }
}