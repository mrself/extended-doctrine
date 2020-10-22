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

    protected function __construct()
    {
    }

    public static function make(array $options = [])
    {
        $options = $options + ['providers' => []];
        $instance = new static();
        $instance->setProviders($options['providers']);
        $instance->init();
        return $instance;
    }

    public function init()
    {
        $this->indexProviders();
    }

    public function create(string $class, array $source = []): EntityInterface
    {
        return FixtureCreator::make([
            'defaults' => $this->getDefaults($class),
            'source' => $source,
            'class' => $class,
            'nestedCallback' => function (string $class, array $source) {
                return $this->create($class, $source);
            }
        ])->create();
    }

    private function getDefaults(string $class): array
    {
        $provider = $this->getProvider($class);
        if ($provider) {
            return $provider->getDefaults();
        }

        return [];
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

    private function getProvider(string $class): ?FixtureDataProviderInterface
    {
        $providerClass = $this->indexedProviders[$class] ?? null;
        return $providerClass ? new $providerClass : null;
    }
}