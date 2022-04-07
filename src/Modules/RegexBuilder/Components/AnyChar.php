<?php
namespace LightWine\Modules\RegexBuilder\Components;

class AnyChar extends CharBase
{
	/**
	 * @return string
	 */
	public function __toString()
	{
		return "." . $this->getModifierString();
	}
}