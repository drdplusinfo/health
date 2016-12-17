<?php
namespace DrdPlus\Health\Afflictions\Effects\EnumTypes;

use Doctrineum\Scalar\ScalarEnumType;
use DrdPlus\Health\Afflictions\Effects\BleedingEffect;
use DrdPlus\Health\Afflictions\Effects\ColdEffect;
use DrdPlus\Health\Afflictions\Effects\CrackedBonesEffect;
use DrdPlus\Health\Afflictions\Effects\HungerEffect;
use DrdPlus\Health\Afflictions\Effects\PainEffect;
use DrdPlus\Health\Afflictions\Effects\SeveredArmEffect;
use DrdPlus\Health\Afflictions\Effects\ThirstEffect;

class AfflictionEffectType extends ScalarEnumType
{
    const AFFLICTION_EFFECT = 'affliction_effect';

    public static function registerSelf()
    {
        parent::registerSelf();
        self::registerSubTypeEnum(BleedingEffect::class, '~^' . BleedingEffect::BLEEDING_EFFECT . '$~');
        self::registerSubTypeEnum(ColdEffect::class, '~^' . ColdEffect::COLD_EFFECT . '$~');
        self::registerSubTypeEnum(CrackedBonesEffect::class, '~^' . CrackedBonesEffect::CRACKED_BONES_EFFECT . '$~');
        self::registerSubTypeEnum(HungerEffect::class, '~^' . HungerEffect::HUNGER_EFFECT . '$~');
        self::registerSubTypeEnum(ThirstEffect::class, '~^' . ThirstEffect::THIRST_EFFECT . '$~');
        self::registerSubTypeEnum(PainEffect::class, '~^' . PainEffect::PAIN_EFFECT . '$~');
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