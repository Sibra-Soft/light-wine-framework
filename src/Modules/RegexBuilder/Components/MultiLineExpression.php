<?php
namespace LightWine\Modules\RegexBuilder\Components;

class MultiLineExpression extends Expression
{
	public function __construct()
	{
		$this->modify('m');
	}

	/**
	 * Asserts that cursor is at the start of the string.
	 *
	 * @return $this
	 */
	public function StartOfString()
	{
		$this->elements[] = '\A';
		return $this;
	}

	/**
	 * Asserts that cursor is at the end of the string.
	 *
	 * @return $this
	 */
	public function EndOfString()
	{
		$this->elements[] = '\Z';
		return $this;
	}

	/**
	 * Asserts that cursor is at the end of the string or just before a newline which is the last character.
	 *
	 * @return $this
	 */
	public function EndOfStringOrNewlineAtEnd()
	{
		$this->elements[] = '\z';
		return $this;
	}

	/**
	 * Asserts that cursor is at the start of the string or of a line.
	 *
	 * @return $this
	 */
	public function StartOfStringOrLine()
	{
		$this->elements[] = '^';
		return $this;
	}

	/**
	 * Asserts that cursor is at the end of the string or of a line.
	 *
	 * @return $this
	 */
	public function EndOfStringOrLine()
	{
		$this->elements[] = '$';
		return $this;
	}
}
