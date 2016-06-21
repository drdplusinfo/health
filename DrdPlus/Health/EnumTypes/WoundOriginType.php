<?php
namespace DrdPlus\Health\EnumTypes;

use Doctrineum\String\StringEnumType;
use DrdPlus\Health\OrdinaryWoundOrigin;
use DrdPlus\Health\SeriousWoundOrigin;

class WoundOriginType extends StringEnumType
{
    const WOUND_ORIGIN = 'wound_origin';

    public static function registerSelf()
    {
        parent::registerSelf();
        self::registerSubTypeEnum(OrdinaryWoundOrigin::class, '~^' . OrdinaryWoundOrigin::ORDINARY . '$~');
        self::registerSubTypeEnum(
            SeriousWoundOrigin::class,
            '~^(?:(?!' . OrdinaryWoundOrigin::ORDINARY . ').)+$~' // just not the "ordinary" string
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::WOUND_ORIGIN;
    }

}