<?php
namespace LightWine\Modules\RegexBuilder\Components;

class Group extends Quantifiable
{
	protected $name;

	/** @var  array */
	protected $elements = array();

	/** @var bool Treat elements as alternatives */
	protected $alt = false;

	/** @var bool Capture? */
	protected $capture = true;

	/**
	 * @param $args
	 */
	public function __construct($name = null)
	{
		$this->name = $name;
	}

	/* -- shared with RExpression -- */

	/**
	 * Turn this group into a set of alternatives (or)
	 *
	 * @return $this
	 */
	public function OneOfThese()
	{
		$this->alt = true;
		return $this;
	}

	/**
	 * Match a string of plain text.
	 *
	 * @param string $text
	 * @return $this
	 */
	public function Text($text)
	{
		$escape = preg_replace('/([\\\\\\^\\$\\.\\[\\]\\|\\(\\)\\?\\*\\+\\{\\}])/', '\\\\$1', $text);

		$this->elements[] = $escape;
		return $this;
	}

	/**
	 * Add an expression as it would be typed in a regular expression.
	 *
	 * @param string $expression
	 * @return $this
	 */
	public function Raw($expression)
	{
		$this->elements[] = $expression;
		return $this;
	}

	/**
	 * Match one of the characters specified in $Chars.
	 *
     * @param Chars $Chars
	 * @return $this
	 */
	public function InChars(Chars $Chars)
	{
		$this->elements[] = $Chars;
		return $this;
	}

	/**
	 * Match one of the characters _not_ specified in $Chars.
	 *
     * @param Chars $Chars
	 * @return $this
	 */
	public function NotInChars(Chars $Chars)
	{
		$Chars->not();
		$this->elements[] = $Chars;
		return $this;
	}

	/**
	 * Match one of the characters specified in $Chars.
	 * Alternative wording for self::inChars() to express the fact that
	 * you will be using just a single character.
	 *
     * @param CharBase $Chars Either RAnyChar or RChars
	 * @return $this
	 */
	public function Char(CharBase $Chars)
	{
		$this->elements[] = $Chars;
		return $this;
	}

	/**
	 * On the one side of the cursor is a word character (letter, digit, or underscore),
	 * on the other side a non-word character.
	 *
	 * @return $this
	 */
	public function WordBoundary()
	{
		$this->elements[] = '\b';
		return $this;
	}

	/**
	 * The cursor is not at a @see RChars::wordBoundary
	 *
	 * @return $this
	 */
	public function AnythingButWordBoundary()
	{
		$this->elements[] = '\B';
		return $this;
	}

	/**
	 * Start a subpattern.
	 *
     * @param Group $Group
	 * @return $this
	 */
	public function Group(Group $Group)
	{
		$this->elements[] = $Group;
		return $this;
	}

	/**
	 * Assert that the expression $LookAhead is matched _after_ the cursor,
	 * but do not "eat" it.
	 *
     * @param LookAhead $LookAhead
	 * @return $this;
	 */
	public function LookAhead(LookAhead $LookAhead)
	{
		$this->elements[] = $LookAhead;
		return $this;
	}

	/**
	 * Assert that the expression $LookBehind is matched _before_ the cursor,
	 * but do not "eat" it.
	 *
     * @param LookBehind $LookBehind
	 * @return $this;
	 */
	public function LookBehind(LookBehind $LookBehind)
	{
		$this->elements[] = $LookBehind;
		return $this;
	}

	/**
	 * Convenience method to match 1 or more whitespace characters.
	 *
	 * @return $this
	 */
	public function Whitespace()
	{
		$this->elements[] = '\\s+';
		return $this;
	}

	/**
	 * Convenience method to match 0 or more whitespace characters.
	 *
	 * @return $this
	 */
	public function OptionalWhitespace()
	{
		$this->elements[] = '\\s*';
		return $this;
	}

	/**
	 * Match the same characters as matched by the $index-th subpattern (group).
	 *
	 * @param int $index The index of a captured group the the expression
	 * @return $this
	 */
	public function BackReference($index)
	{
		$this->elements[] = '\\' . $index;
		return $this;
	}

	/* -- end shared with RExpression -- */

	/**
	 * Asserts start of line.
	 *
	 * @return $this
	 */
	public function StartOfLine()
	{
		$this->elements[] = '^';
		return $this;
	}

	/**
	 * Asserts end of line.
	 *
	 * @return $this
	 */
	public function EndOfLine()
	{
		$this->elements[] = '$';
		return $this;
	}

	/**
	 * Make this group 'non-capturing', i.e. it will not end up in the search results.
	 *
	 * @return Group
	 */
	public function DontCapture()
	{
		$this->capture = false;
		return $this;
	}

	/**
	 * Returns the prefix of the expression, based on its type.
	 *
	 * @return string
	 */
	protected function GetPrefix()
	{
		if (!$this->capture) {
			return "?:";
		} elseif (is_null($this->name)) {
			return "";
		} else {
			return "?P<{$this->name}>";
		}
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$delimiter = $this->alt ? '|' : '';

		return "(" . $this->GetPrefix() . implode($delimiter, $this->elements) . ")" . $this->GetModifierString();
	}
}