<?php declare(strict_types = 1);

namespace ProLib\DataGrid;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\Column\Column;
use WebChemistry\I18N\DateFormatterInterface;

final class DataGridBuilder {

	/** @var EntityManagerInterface */
	private $em;

	/** @var ClassMetadata */
	private $metadata;

	/** @var DataGrid */
	private $grid;

	/** @var string */
	private $id;

	/** @var QueryBuilder */
	private $queryBuilder;

	/** @var DateFormatterInterface */
	private $formatter;

	/** @var string[] */
	private $orderBy = [];

	public function __construct(string $entity, EntityManagerInterface $em, IDataGridFactory $dataGridFactory, DateFormatterInterface $formatter) {
		$this->em = $em;
		$this->formatter = $formatter;

		$this->metadata = $this->em->getClassMetadata($entity);
		$this->grid = $dataGridFactory->create();

		$this->id = $this->metadata->getIdentifier()[0];
		$this->createColumn($this->id, '#', $this->metadata->getFieldMapping($this->id)['type']);

		$this->queryBuilder = $this->em->getRepository($entity)->createQueryBuilder('e');

		$this->orderBy = [
			$this->id => 'DESC',
		];
	}

	public function reverseOrderBy(): self {
		$this->orderBy = [
			$this->id => 'ASC',
 		];

		return $this;
	}

	public function getGrid(): DataGrid {
		return $this->grid;
	}

	public function getQueryBuilder(): QueryBuilder {
		return $this->queryBuilder;
	}

	public function setQueryBuilder(QueryBuilder $queryBuilder): void {
		$this->queryBuilder = $queryBuilder;
	}

	protected function finalize(): void {
		$this->grid->setDataSource($this->queryBuilder);

		$this->grid->setDefaultSort($this->orderBy);
	}

	public function setColumns(array $columns): self {
		foreach ($columns as $name => $caption) {
			if (is_array($caption)) {
				$this->addColumn($name, ...$caption);
			} else {
				$this->addColumn($name, $caption);
			}
		}

		return $this;
	}

	private function callbackIdentifierDelete($id, ?callable $beforeDelete = null): void {
		$entity = $this->em->getRepository($this->metadata->getName())->find($id);

		if ($entity) {
			if ($beforeDelete) {
				$beforeDelete($entity);
			}

			$this->em->remove($entity);
			$this->em->flush();
		}
	}

	public function createIdentifierDelete(?callable $callback = null, ?callable $beforeDelete = null): void {
		if (!$callback) {
			$callback = function ($id) use ($beforeDelete): void {
				$this->callbackIdentifierDelete($id, $beforeDelete);
			};
		}

		$this->grid->addDeleteAction($callback, $this->metadata->getFieldMapping($this->id)['type']);
	}

	public function addEditAction(string $link = 'edit', ?array $params = null): self {
		$this->grid->addEditAction($link, $params);

		return $this;
	}

	public function getColumn(string $name): Column {
		return $this->grid->getColumn($name);
	}

	public function addColumn(string $name, string $caption, array $options = []): Column {
		$mapping = $this->metadata->getFieldMapping($name);
		$type = $options['type'] ?? $mapping['type'];

		$this->createColumn($name, $caption, $type);
		$this->applyFilter($name, $type, $options);

		return $this->grid->getColumn($name);
	}

	public function build(): DataGrid {
		$this->finalize();

		return $this->grid;
	}

	protected function applyFilter(string $name, string $type, array $options): void {
		$column = $this->grid->getColumn($name);
		switch ($type) {
			case 'integer':
			case 'boolean':
				break;
			case 'html':
			case 'text':
			case 'string':
				$column->setFilterText();
				break;
			case 'datetime':
			case 'date':
				if ($options['range'] ?? false) {
					$column->setFilterDateRange();
				} else {
					$column->setFilterDate();
				}
				break;
			default:
				throw new InvalidArgumentException('Column ' . $type . ' is not supported');
		}
	}

	protected function createColumn(string $name, string $caption, string $type): Column {
		switch ($type) {
			case 'integer':
				return $this->grid->addColumnNumber($name, $caption)
						->setSortable();
			case 'string':
				return $this->grid->addColumnText($name, $caption)
						->setSortable();
			case 'boolean':
				return $this->grid->addColumnBoolean($name, $caption);
			case 'text':
				return $this->grid->addColumnText($name, $caption)
					->setSortable()
					->setRenderer(function ($entity) use ($name) {
						$value = $this->propertyAccess($entity, $name);

						return Strings::truncate($value, 150);
					});
			case 'html':
				return $this->grid->addColumnText($name, $caption)
					->setSortable()
					->setRenderer(function ($entity) use ($name) {
						$value = $this->propertyAccess($entity, $name);

						return Strings::truncate(strip_tags($value), 150);
					});
			case 'date':
				return $this->grid->addColumnDateTime($name, $caption)
					->setFormat($this->formatter->getDateFormat());
			case 'datetime':
				return $this->grid->addColumnDateTime($name, $caption)
					->setFormat($this->formatter->getDateTimeFormat());
			default:
				throw new InvalidArgumentException('Column ' . $type . ' is not supported');
		}
	}

	protected function propertyAccess($entity, string $column) {
		if (is_object($entity)) {
			return $entity->$column;
		}

		if (is_array($entity)) {
			return $entity[$column];
		}

		return $entity;
	}

}
