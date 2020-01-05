<?php declare(strict_types = 1);

namespace ProLib\DataGrid\Columns;

use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;

class BooleanColumn extends Column {

	public static $defaultTrueClass = 'ti-check text-success';
	public static $defaultFalseClass = 'ti-close text-danger';

	/** @var string|null */
	private $classes = [];

	public function __construct(DataGrid $grid, string $key, string $column, string $name) {
		parent::__construct($grid, $key, $column, $name);

		$this->classes = [
			false => self::$defaultFalseClass,
			true => self::$defaultTrueClass,
		];
	}

	public function render(Row $row) {
		$item = Html::el('i');

		$item->setAttribute('class', $this->getColumnValue($row) ? self::$defaultTrueClass : self::$defaultFalseClass);

		return $item;
	}

	public function setBooleanClasses(?string $true, ?string $false) {
		 if ($true) {
		 	$this->classes[true] = $true;
		 }
		 if ($false) {
		 	$this->classes[false] = $false;
		 }

		return $this;
	}

	public function getAlign(): string {
		return 'center';
	}

}
