<?php
namespace LightWine\Modules\RegexBuilder\Components;

class Expression
{
	/** @var string Modifiers like 'm', 's', and 'i' */
	protected $modifiers = '';

	/** @var array The sequential or alternate elements of the expression */
	protected $elements = array();

	/** @var bool Treat elements as alternatives */
	protected $alt = false;

	/* -- shared with RGroup -- */

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

	/* -- end shared with RGroup -- */

	/**
	 * Expert use: add a modifier by its letter.
	 * See also: http://nl3.php.net/manual/en/reference.pcre.pattern.modifiers.php
	 *
	 * @param string $modifier A single character
	 * @return $this
	 */
	public function Modify($modifier)
	{
		$this->modifiers .= $modifier;
		return $this;
	}

	/**
	 * Match all letters in this expression case insensitive,
	 *
	 * @return $this
	 */
	public function ModifyCaseInsensitive()
	{
		$this->modifiers .= 'i';
		return $this;
	}

	/**
	 * If used, the "any char" (.) does not match \n
	 *
	 * @return $this
	 */
	public function ModifyAnyCharAcceptsNewlines()
	{
		$this->modifiers .= 's';
		return $this;
	}

	/**
	 * Pattern strings are treated as UTF-8
	 *
	 * @return $this
	 */
	public function ModifyTreatAsUnicode()
	{
		$this->modifiers .= 'u';
		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$delimiters = '/#%~`&';
		$delimiter = $this->alt ? '|' : '';

		$exp = implode($delimiter, $this->elements);

		$selectedDelimiter = null;
		for ($i = 0; $i < strlen($delimiters); $i++) {
			$delimiter = $delimiters[$i];
			if (strpos($exp, $delimiter) === false) {
				$selectedDelimiter = $delimiter;
				break;
			}
		}

		if ($selectedDelimiter === null) {
			trigger_error('Cannot find a suitable delimiter', E_USER_ERROR);
		}

		return $delimiter . $exp . $delimiter . $this->modifiers;
	}
}
