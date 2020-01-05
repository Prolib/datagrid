<?php declare(strict_types = 1);

namespace ProLib\DataGrid\DI;

use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ProLib\DataGrid\IDataGridBuilderFactory;
use ProLib\DataGrid\IDataGridFactory;

final class DataGridExtension extends CompilerExtension {

	/** @var string[] */
	private const SETTERS = [
		'strictSessionsValues' => 'setStrictSessionFilterValues',
		'rememberState' => 'setRememberState',
	];

	public function getConfigSchema(): Schema {
		return Expect::structure([
			'strictSessionsValues' => Expect::bool(true),
			'rememberState' => Expect::bool(true),
		]);
	}

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$config = (array) $this->getConfig();

		$def = $builder->addFactoryDefinition($this->prefix('dataGridFactory'))
			->setImplement(IDataGridFactory::class)
			->getResultDefinition();

		foreach (self::SETTERS as $name => $setter) {
			$def->addSetup($setter, [$config[$name]]);
		}

		$builder->addFactoryDefinition($this->prefix('dataGridBuilderFactory'))
			->setImplement(IDataGridBuilderFactory::class);
	}

}
