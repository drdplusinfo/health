<?php
namespace DrdPlus\Person\Health\Affliction;

use Doctrineum\String\StringEnum;
use Granam\Tools\ValueDescriber;

class Source extends StringEnum
{
    const SOURCE = 'source';

    const EXTERNAL = 'external';

    /**
     * @return Source
     */
    public static function getExternalSource()
    {
        return self::getEnum(self::EXTERNAL);
    }

    const ACTIVE = 'active';

    /**
     * @return Source
     */
    public static function getActiveSource()
    {
        return self::getEnum(self::ACTIVE);
    }

    const PASSIVE = 'passive';

    /**
     * @return Source
     */
    public static function getPassiveSource()
    {
        return self::getEnum(self::PASSIVE);
    }

    const DEFORMATION = 'deformation';

    /**
     * @return Source
     */
    public static function getDeformationSource()
    {
        return self::getEnum(self::DEFORMATION);
    }

    /**
     * @param bool|float|int|object|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Affliction\Exceptions\UnknownAfflictionSource
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, [self::EXTERNAL, self::PASSIVE, self::ACTIVE, self::DEFORMATION], true)) {
            throw new Exceptions\UnknownAfflictionSource(
                'Unexpected source of an affliction: ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

}