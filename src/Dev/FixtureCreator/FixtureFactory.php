<?php declare(strict_types=1);

namespace Mrself\ExtendedDoctrine\Dev\FixtureCreator;

use Mrself\ExtendedDoctrine\Entity\EntityInterface;
use Mrself\ExtendedDoctrine\Metadata\Property\TypeDefiner;

class FixtureFactory
{

    /**
     * @var string[]
     */
    private $providers = [];

    /**
     * @var array
     */
    private $indexedProviders = [];

    public function init()
    {
        $this->indexProviders();
    }

    public function create(string $class, array $source): EntityInterface
    {
        $provider = $this->getProvider($class);

        return FixtureCreator::make([
            'defaults' => $provider->getDefaults(),
            'source' => $source,
            'class' => $class,
            'nestedCallback' => function (string $class, array $source) {
                return $this->create($class, $source);
            }
        ])->create();
    }

    /**
     * @param string[] $providers
     */
    public function setProviders(array $providers)
    {
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    public function addProvider(string $provider)
    {
        $this->providers[] = $provider;
    }

    private function indexProviders()
    {
        foreach ($this->providers as $provider) {
            $this->indexedProviders[$provider::getClass()] = $provider;
        }
    }

    private function getProvider(string $class): FixtureDataProviderInterface
    {
        $providerClass = $this->indexedProviders[$class];
        return new $providerClass;
    }
}