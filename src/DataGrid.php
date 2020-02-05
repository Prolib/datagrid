<?php declare(strict_types = 1);

namespace ProLib\DataGrid;

use ProLib\DataGrid\Columns\BooleanColumn;
use ProLib\DataGrid\Columns\ImageColumn;
use Ublaboo\DataGrid\Column\Action;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\DataGrid as DataGridAlias;

class DataGrid extends DataGridAlias {

	public function addColumnBoolean(string $key, string $name, ?string $column = null): BooleanColumn {
		$column = $column ?: $key;
		$this->addColumn($key, $control = new BooleanColumn($this, $key, $column, $name));

		return $control;
	}

	public function addColumnImage(string $key, string $name, ?string $column = null): ImageColumn {
		$column = $column ?: $key;
		$this->addColumn($key, $control = new ImageColumn($this, $key, $column, $name));

		return $control;
	}

	public function addEditAction(string $link = 'edit', ?array $params = null): Action {
		return $this->addAction('edit', 'upravit', $link, $params)
			->setClass('btn btn-primary btn-sm');
	}

	public function addSmallAction(string $key, string $name, ?string $href = null, ?array $params = null): Action {
		return $this->addAction($key, $name, $href, $params)
				->setClass('btn btn-default btn-secondary btn-sm');
	}

	public function addDeleteAction(callable $callback, string $castTo = 'int') {
		$this->addActionCallback('delete', 'odstranit', function ($id) use ($callback, $castTo) {
			if (!$id) {
				$this->redirect('this');
			}

			settype($id, $castTo);

			$callback($id);

			$this->getPresenter()->flashMessage('Položka odstraněna.');
			$this->redirect('this');
		})
			->setConfirmation(new StringConfirmation('Jste si opravdu jistí?'))
			->setClass('btn btn-danger btn-sm');
	}

	public function addLinkAction(string $key, string $name, string $address, ?array $params = []) {
		return $this->addAction($key, $name, $address, $params)
			->addAttributes(['target' => '_blank'])
			->setClass('btn btn-primary btn-sm');
	}

	public function addColumnNumber(string $key, string $name, ?string $column = null): ColumnNumber {
		$column = parent::addColumnNumber($key, $name, $column);
		$column->setAlign('left');

		return $column;
	}

}
