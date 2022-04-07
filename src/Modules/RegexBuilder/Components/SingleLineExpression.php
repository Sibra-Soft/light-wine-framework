<?php
namespace LightWine\Modules\RegexBuilder\Components;

class SingleLineExpression extends Expression
{
	/**
	 * Asserts that cursor is at the start of the string.
	 *
	 * @return $this
	 */
	public function StartOfString()
	{
		$this->elements[] = '^';
		return $this;
	}

	/**
	 * Asserts that cursor is at the end of the string.
	 *
	 * @return $this
	 */
	public function EndOfString()
	{
		$this->elements[] = '$';
		return $this;
	}
}