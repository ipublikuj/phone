<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\Phone;
use Nette;
use Nette\DI;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;

abstract class BaseTestCase extends BaseMockeryTestCase
{

	/** @var string[] */
	protected array $additionalConfigs = [];

	/** @var DI\Container */
	private DI\Container $container;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->container = $this->createContainer($this->additionalConfigs);
	}

	/**
	 * @return DI\Container
	 */
	protected function getContainer(): DI\Container
	{
		return $this->container;
	}

	/**
	 * @param string[] $additionalConfigs
	 *
	 * @return Nette\DI\Container
	 */
	protected function createContainer(array $additionalConfigs = []): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../common.neon');

		foreach ($additionalConfigs as $additionalConfig) {
			if ($additionalConfig && file_exists($additionalConfig)) {
				$config->addConfig($additionalConfig);
			}
		}

		Phone\DI\PhoneExtension::register($config);

		return $config->createContainer();
	}

	/**
	 * @param string $serviceType
	 * @param object $serviceMock
	 *
	 * @return void
	 */
	protected function mockContainerService(
		string $serviceType,
		object $serviceMock
	): void {
		$foundServiceNames = $this->getContainer()->findByType($serviceType);

		foreach ($foundServiceNames as $serviceName) {
			$this->replaceContainerService($serviceName, $serviceMock);
		}
	}

	/**
	 * @param string $serviceName
	 * @param object $service
	 *
	 * @return void
	 */
	private function replaceContainerService(string $serviceName, object $service): void
	{
		$this->getContainer()->removeService($serviceName);
		$this->getContainer()->addService($serviceName, $service);
	}

}
