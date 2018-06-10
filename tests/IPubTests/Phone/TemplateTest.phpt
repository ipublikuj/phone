<?php
/**
 * Test: IPub\Phone\Template
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Phone!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           30.01.15
 */

declare(strict_types = 1);

namespace IPubTests\Phone;

use Nette;
use Nette\Application;
use Nette\Application\Routers;
use Nette\Application\UI;
use Nette\Utils;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Phone;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require __DIR__ . DS . 'libs' . DS . 'RouterFactory.php';

/**
 * Phone number template helpers and macros tests
 *
 * @package        iPublikuj:Phone!
 * @subpackage     Tests
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
class TemplateTest extends Tester\TestCase
{
	/**
	 * @var Application\IPresenterFactory
	 */
	private $presenterFactory;

	/**
	 * {@inheritdoc}
	 */
	public function setUp()
	{
		parent::setUp();

		$dic = $this->createContainer();

		// Get presenter factory from container
		$this->presenterFactory = $dic->getByType('Nette\Application\IPresenterFactory');
	}

	public function testTemplateHelper()
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Create GET request
		$request = new Application\Request('Test', 'GET', ['action' => 'useHelper']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		$dq = Tester\DomQuery::fromHtml((string) $response->getSource());

		Assert::true($dq->has('div[id*="value"]'));

		// Get all styles element
		$container = $dq->find('div[id*="value"]');

		Assert::equal('+32 16 12 34 56', (string) $container[0]);
	}

	public function testTemplateMacro()
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Create GET request
		$request = new Application\Request('Test', 'GET', ['action' => 'useMacro']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		$dq = Tester\DomQuery::fromHtml((string) $response->getSource());

		Assert::true($dq->has('div[id*="value"]'));

		// Get all styles element
		$container = $dq->find('div[id*="value"]');

		Assert::equal('+32 16 12 34 56', (string) $container[0]);
	}

	/**
	 * @return Application\IPresenter
	 */
	protected function createPresenter() : Application\IPresenter
	{
		// Create test presenter
		$presenter = $this->presenterFactory->createPresenter('Test');
		// Disable auto canonicalize to prevent redirection
		$presenter->autoCanonicalize = FALSE;

		return $presenter;
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Phone\DI\PhoneExtension::register($config);

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'config.neon');

        $config->addConfig(__DIR__ . DS . 'files' . DS . 'presenters.neon');

		return $config->createContainer();
	}
}

class TestPresenter extends UI\Presenter
{
	public function renderUseHelper()
	{
		// Set template for template helper testing
		$this->template->setFile(__DIR__ . DS . 'templates' . DS . 'helper.latte');
	}

	public function renderUseMacro()
	{
		// Set template for template macro testing
		$this->template->setFile(__DIR__ . DS . 'templates' . DS . 'macro.latte');
	}
}

\run(new TemplateTest());
