<?php declare(strict_types = 1);

namespace ProLib\DataGrid;

interface IDataGridFactory {

	public function create(): DataGrid;

}
