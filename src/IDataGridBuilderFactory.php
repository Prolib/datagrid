<?php declare(strict_types = 1);

namespace ProLib\DataGrid;

interface IDataGridBuilderFactory {

	public function create(string $entity): DataGridBuilder;

}
