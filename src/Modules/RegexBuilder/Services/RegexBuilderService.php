<?php
namespace LightWine\Modules\RegexBuilder\Services;

use LightWine\Modules\RegexBuilder\Components\SingleLineExpression;
use LightWine\Modules\RegexBuilder\Components\MultiLineExpression;
use LightWine\Modules\RegexBuilder\Components\Group;
use LightWine\Modules\RegexBuilder\Components\LookAhead;
use LightWine\Modules\RegexBuilder\Components\LookBehind;
use LightWine\Modules\RegexBuilder\Components\AnyChar;
use LightWine\Modules\RegexBuilder\Components\Chars;

class RegexBuilderService
{
    /**
     * @return SingleLineExpression
     */
	public static function Expression()
	{
		return new SingleLineExpression();
	}

	/**
     * @return MultiLineExpression
     */
	public static function MultiLineExpression()
	{
		return new MultiLineExpression();
	}

	/**
     * @param string $name
     * @return Group
     */
	public static function Group($name = null)
	{
		return new Group($name);
	}

	/**
     * @return LookAhead
     */
	public static function LookAhead()
	{
		return new LookAhead();
	}

	/**
     * @return LookBehind
     */
	public static function LookBehind()
	{
		return new LookBehind();
	}

	/**
     * @return AnyChar
     */
	public static function AnyChar()
	{
		return new AnyChar();
	}

	/**
     * @param string $chars
     * @return Chars
     */
	public static function Chars($chars = '')
	{
		return new Chars($chars);
	}
}