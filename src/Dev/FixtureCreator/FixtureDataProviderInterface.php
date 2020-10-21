<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Dev\FixtureCreator;

interface FixtureDataProviderInterface
{
    public function getDefaults(): array;

    public static function getClass(): string;
}