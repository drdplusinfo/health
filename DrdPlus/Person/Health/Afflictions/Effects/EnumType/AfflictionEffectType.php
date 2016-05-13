<?php
namespace DrdPlus\Person\Health\Afflictions\Effects\EnumType;

use Doctrineum\Scalar\ScalarEnumType;
use DrdPlus\Person\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Person\Health\Afflictions\Effects\SeveredArmEffect;

class AfflictionEffectType extends ScalarEnumType
{
    const AFFLICTION_EFFECT = 'affliction_effect';

    public static function registerSelf()
    {
        parent::registerSelf();
        self::registerSubTypeEnum(ColdEffect::class, '~^' . ColdEffect::COLD . '$~');
        self::registerSubTypeEnum(SeveredArmEffect::class, '~^' . SeveredArmEffect::SEVERED_ARM . '$~');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::AFFLICTION_EFFECT;
    }
}