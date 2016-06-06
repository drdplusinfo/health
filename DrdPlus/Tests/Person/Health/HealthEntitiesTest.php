<?php
namespace DrdPlus\Tests\Person\Health;

use Doctrineum\Tests\Entity\AbstractDoctrineEntitiesTest;
use DrdPlus\Codes\RaceCodes;
use DrdPlus\Person\Health\Afflictions\AfflictionSize;
use DrdPlus\Person\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Person\Health\Afflictions\ElementalPertinence\WaterPertinence;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Bleeding;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Cold;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\CrackedBones;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\Pain;
use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\SeveredArm;
use DrdPlus\Person\Health\EnumTypes\PersonHealthEnumsRegistrar;
use DrdPlus\Person\Health\Health;
use DrdPlus\Person\Health\SpecificWoundOrigin;
use DrdPlus\Person\Health\WoundSize;
use DrdPlus\Properties\Base\Strength;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Properties\Derived\WoundBoundary;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use DrdPlus\Tables\Races\RacesTable;

class HealthEntitiesTest extends AbstractDoctrineEntitiesTest
{
    protected function setUp()
    {
        parent::setUp();
        PersonHealthEnumsRegistrar::registerAll();
    }

    protected function getDirsWithEntities()
    {
        return [
            str_replace(DIRECTORY_SEPARATOR . 'Tests', '', __DIR__)
        ];
    }

    protected function createEntitiesToPersist()
    {
        $health = new Health(
            new WoundBoundary(
                new Toughness(new Strength(3), RaceCodes::ORC, RaceCodes::GOBLIN, new RacesTable()),
                new WoundsTable()
            )
        );
        $ordinaryWound = $health->createWound(new WoundSize(1), SpecificWoundOrigin::getMechanicalCutWoundOrigin());
        $seriousWound = $health->createWound(new WoundSize(7), SpecificWoundOrigin::getMechanicalCrushWoundOrigin());
        $health->addAffliction($bleeding = Bleeding::createIt($seriousWound));
        $health->addAffliction($cold = Cold::createIt($seriousWound));
        $health->addAffliction($crackedBones = CrackedBones::createIt($seriousWound));
        $health->addAffliction($pain = Pain::createIt($seriousWound, AfflictionVirulence::getDayVirulence(), AfflictionSize::getIt(5), WaterPertinence::getPlus()));
        $health->addAffliction($severedArm = SeveredArm::createIt($seriousWound));

        return [
            $health,
            $ordinaryWound,
            $seriousWound,
            $ordinaryWound->getPointsOfWound()->last(),
            $bleeding,
            $cold,
            $crackedBones,
            $pain,
            $severedArm,
        ];
    }

}