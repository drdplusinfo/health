<?php
namespace DrdPlus\Person\Health\Afflictions;

use Doctrineum\String\StringEnum;
use Granam\Tools\ValueDescriber;

class AfflictionSource extends StringEnum
{
    const SOURCE = 'source';

    /**
     * @param string $sourceCode
     * @return AfflictionSource
     */
    public static function getIt($sourceCode)
    {
        return static::getEnum($sourceCode);
    }

    const EXTERNAL = 'external';

    /**
     * @return AfflictionSource
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
     * @return AfflictionSource
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
     * @return AfflictionSource
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
     * @return AfflictionSource
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
     * @return AfflictionSource
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
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Person\Health\Afflictions\Exceptions\UnknownAfflictionSource
     */
    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, [self::EXTERNAL, self::PASSIVE, self::ACTIVE, self::PARTIAL_DEFORMATION, self::FULL_DEFORMATION], true)) {
            throw new Exceptions\UnknownAfflictionSource(
                'Unexpected source of an affliction: ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

}