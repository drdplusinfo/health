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
    public const AFFLICTION_EFFECT = 'affliction_effect';

    /**
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function registerSelf(): bool
    {
        $registered = parent::registerSelf();
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::registerSubTypeEnum(BleedingEffect::class, '~^' . BleedingEffect::BLEEDING_EFFECT . '$~');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::registerSubTypeEnum(ColdEffect::class, '~^' . ColdEffect::COLD_EFFECT . '$~');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::registerSubTypeEnum(CrackedBonesEffect::class, '~^' . CrackedBonesEffect::CRACKED_BONES_EFFECT . '$~');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::registerSubTypeEnum(HungerEffect::class, '~^' . HungerEffect::HUNGER_EFFECT . '$~');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::registerSubTypeEnum(ThirstEffect::class, '~^' . ThirstEffect::THIRST_EFFECT . '$~');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::registerSubTypeEnum(PainEffect::class, '~^' . PainEffect::PAIN_EFFECT . '$~');
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        self::registerSubTypeEnum(SeveredArmEffect::class, '~^' . SeveredArmEffect::SEVERED_ARM_EFFECT . '$~');

        return $registered;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::AFFLICTION_EFFECT;
    }
}