<?php
namespace LightWine\Modules\RegexBuilder\Components;

class LookBehind extends Group
{
	private $positive = true;

	public function __construct()
	{
	}

	/**
	 * Do _not_ match the contents of this look ahead.
	 *
	 * @return $this
	 */
	public function Negative()
	{
		$this->positive = false;
		return $this;
	}

	/**
	 * Returns the prefix of the expression, based on its type.
	 *
	 * @return string
	 */
	protected function GetPrefix()
	{
		return $this->positive ? '?<=' : '?<!';
	}
}
