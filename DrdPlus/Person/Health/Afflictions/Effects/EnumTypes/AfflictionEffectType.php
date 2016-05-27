<?php
namespace DrdPlus\Person\Health\Afflictions\Effects\EnumTypes;

use Doctrineum\Scalar\ScalarEnumType;
use DrdPlus\Person\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Person\Health\Afflictions\Effects\SeveredArmEffect;

class AfflictionEffectType extends ScalarEnumType
{
    const AFFLICTION_EFFECT = 'affliction_effect';

    public static function registerSelf()
    {
        parent::registerSelf();
        self::registerSubTypeEnum(ColdEffect::class, '~^' . ColdEffect::COLD_EFFECT . '$~');
        self::registerSubTypeEnum(SeveredArmEffect::class, '~^' . SeveredArmEffect::SEVERED_ARM_EFFECT . '$~');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::AFFLICTION_EFFECT;
    }
}