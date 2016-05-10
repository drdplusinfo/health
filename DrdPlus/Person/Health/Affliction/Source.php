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

    /**
     * @return bool
     */
    public function isExternal()
    {
        return $this->getValue() === self::EXTERNAL;
    }

    const ACTIVE = 'active';

    /**
     * @return Source
     */
    public static function getActiveSource()
    {
        return self::getEnum(self::ACTIVE);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->getValue() === self::ACTIVE;
    }

    const PASSIVE = 'passive';

    /**
     * @return Source
     */
    public static function getPassiveSource()
    {
        return self::getEnum(self::PASSIVE);
    }

    /**
     * @return bool
     */
    public function isPassive()
    {
        return $this->getValue() === self::PASSIVE;
    }

    const PARTIAL_DEFORMATION = 'partial_deformation';

    /**
     * @return Source
     */
    public static function getPartialDeformationSource()
    {
        return self::getEnum(self::PARTIAL_DEFORMATION);
    }

    /**
     * @return bool
     */
    public function isPartialDeformation()
    {
        return $this->getValue() === self::PARTIAL_DEFORMATION;
    }

    const FULL_DEFORMATION = 'full_deformation';

    /**
     * @return Source
     */
    public static function getFullDeformationSource()
    {
        return self::getEnum(self::FULL_DEFORMATION);
    }

    /**
     * @return bool
     */
    public function isFullDeformation()
    {
        return $this->getValue() === self::FULL_DEFORMATION;
    }

    /**
     * @return bool
     */
    public function isDeformation()
    {
        return $this->isPartialDeformation() || $this->isFullDeformation();
    }

    /**
     * @param bool|float|int|object|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Affliction\Exceptions\UnknownAfflictionSource
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, [self::EXTERNAL, self::PASSIVE, self::ACTIVE, self::PARTIAL_DEFORMATION], true)) {
            throw new Exceptions\UnknownAfflictionSource(
                'Unexpected source of an affliction: ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

}