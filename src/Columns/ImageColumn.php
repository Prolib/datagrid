<?php declare(strict_types = 1);

namespace ProLib\DataGrid\Columns;

use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use WebChemistry\Images\IImageStorage;
use WebChemistry\Images\Resources\IFileResource;

class ImageColumn extends Column {

	/** @var string */
	public static $defaultImageClass = 'grid-column-image';

	/** @var IImageStorage */
	private $imageStorage;

	/** @var mixed[] */
	private $filters = [];

	public function __construct(DataGrid $grid, string $key, string $column, string $name) {
		parent::__construct($grid, $key, $column, $name);
	}

	public function addFilter(string $name, array $args = []): void {
		$this->filters[$name] = $args;
	}

	protected function getImageStorage(): IImageStorage {
		if (!$this->imageStorage) {
			/** @var IPresenter|Presenter $presenter */
			$presenter = $this->grid->lookup(IPresenter::class);

			$this->imageStorage = $presenter->getContext()->getByType(IImageStorage::class);
		}

		return $this->imageStorage;
	}

	public function render(Row $row) {
		/** @var IFileResource $resource */
		if ($resource = $this->getColumnValue($row)) {
			if ($this->filters) {
				foreach ($this->filters as $name => $args) {
					$resource->setFilter($name, $args);
				}
			}

			return Html::el('img', [
				'src' => $this->getImageStorage()->link($resource),
				'class' => self::$defaultImageClass,
			]);
		} else {
			return '';
		}
	}

}
