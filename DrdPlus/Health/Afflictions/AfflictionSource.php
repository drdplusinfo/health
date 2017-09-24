<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use Granam\Tools\ValueDescriber;

/**
 * @method static AfflictionSource getEnum($value)
 */
class AfflictionSource extends StringEnum
{
    const AFFLICTION_SOURCE = 'affliction_source';

    /**
     * @param string $sourceCode
     * @return AfflictionSource
     */
    public static function getIt($sourceCode): AfflictionSource
    {
        return static::getEnum($sourceCode);
    }

    const EXTERNAL = 'external';

    /**
     * @return AfflictionSource
     */
    public static function getExternalSource(): AfflictionSource
    {
        return self::getEnum(self::EXTERNAL);
    }

    /**
     * @return bool
     */
    public function isExternal(): bool
    {
        return $this->getValue() === self::EXTERNAL;
    }

    const ACTIVE = 'active';

    /**
     * @return AfflictionSource
     */
    public static function getActiveSource(): AfflictionSource
    {
        return self::getEnum(self::ACTIVE);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->getValue() === self::ACTIVE;
    }

    const PASSIVE = 'passive';

    /**
     * @return AfflictionSource
     */
    public static function getPassiveSource(): AfflictionSource
    {
        return self::getEnum(self::PASSIVE);
    }

    /**
     * @return bool
     */
    public function isPassive(): bool
    {
        return $this->getValue() === self::PASSIVE;
    }

    const PARTIAL_DEFORMATION = 'partial_deformation';

    /**
     * @return AfflictionSource
     */
    public static function getPartialDeformationSource(): AfflictionSource
    {
        return self::getEnum(self::PARTIAL_DEFORMATION);
    }

    /**
     * @return bool
     */
    public function isPartialDeformation(): bool
    {
        return $this->getValue() === self::PARTIAL_DEFORMATION;
    }

    const FULL_DEFORMATION = 'full_deformation';

    /**
     * @return AfflictionSource
     */
    public static function getFullDeformationSource(): AfflictionSource
    {
        return self::getEnum(self::FULL_DEFORMATION);
    }

    /**
     * @return bool
     */
    public function isFullDeformation(): bool
    {
        return $this->getValue() === self::FULL_DEFORMATION;
    }

    /**
     * @return bool
     */
    public function isDeformation(): bool
    {
        return $this->isPartialDeformation() || $this->isFullDeformation();
    }

    /**
     * @param bool|float|int|string $enumValue
     * @return string
     * @throws \DrdPlus\Health\Afflictions\Exceptions\UnknownAfflictionSource
     * @throws \Doctrineum\String\Exceptions\UnexpectedValueToEnum
     */
    protected static function convertToEnumFinalValue($enumValue): string
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