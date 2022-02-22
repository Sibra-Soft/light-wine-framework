<?php
namespace LightWine\Modules\Templating\Services;

use LightWine\Core\Helpers\StringHelpers;

class TemplatingEngineService
{
    protected $echoFormat = 'static::e(%s)';
    protected $contentTags = ['{{', '}}'];
    protected $loopsStack = [];
    protected $PhpTag = '<?php ';

    /**
     * This function runs all the compilers of the engine service
     * @param string $content The current template content
     * @param array $variables The current template variables that can be used for replacements
     * @return string The content after running compilers
     */
    public function RunEngineCompilers(string $content, array $variables): string {
        $result = "";

        foreach (token_get_all($content) as $token) {
            $result = is_array($token) ? $this->ParseToken($token) : $token;
        }

        $result = $this->compileRegularEchos($result);

        ob_start();
        extract($variables);

        try {
            eval(' ?>' . $result . '<?php ');
        }
        catch (\Exception $e) {
            $this->handleViewException($e);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Escape HTML entities in a string.
     *
     * @param string $value
     * @return string
     */
    public static function e($value)
    {
        if (\is_array($value) || \is_object($value)) {
            return \htmlentities(\print_r($value, true), ENT_QUOTES, 'UTF-8', false);
        }
        return \htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Compile the "regular" echo statements.
     *
     * @param string $value
     * @return string
     */
    protected function compileRegularEchos($value)
    {
        $pattern = \sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];
            $wrapped = \sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]));
            return $matches[1] ? \substr($matches[0], 1) : $this->PhpTag . 'echo ' . $wrapped . '; ?>' . $whitespace;
        };
        return \preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the default values for the echo statement.
     *
     * @param string $value
     * @return string
     */
    protected function compileEchoDefaults($value)
    {
        return \preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
    }

    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    public static function last($array, callable $callback = null, $default = null)
    {
        if (\is_null($callback)) {
            return empty($array) ? static::value($default) : \end($array);
        }
        return static::first(\array_reverse($array), $callback, $default);
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param array $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    public static function first($array, callable $callback = null, $default = null)
    {
        if (\is_null($callback)) {
            return empty($array) ? static::value($default) : \reset($array);
        }
        foreach ($array as $key => $value) {
            if (\call_user_func($callback, $key, $value)) {
                return $value;
            }
        }
        return static::value($default);
    }

    /**
     * Parse the tokens from the template.
     *
     * @param array $token
     * @return string
     */
    private function ParseToken($token)
    {
        list($id, $content) = $token;
        if ($id == T_INLINE_HTML) {
            $content = $this->CompileStatements($content);
        }
        return $content;
    }

    /**
     * This function gets all the statements in the template and compiles them one-by-one
     * @param string $value The template content
     * @return string The content of the template after running compilers
     */
    protected function CompileStatements(string $value): string {
        $callback = function ($match) {
            if (StringHelpers::Contains($match[1], '@')) {
                $match[0] = isset($match[3]) ? $match[1] . $match[3] : $match[1];
            } elseif (isset($this->customDirectivesRT[$match[1]])) {
                if ($this->customDirectivesRT[$match[1]] == true) {
                    $match[0] = $this->compileStatementCustom($match);
                } else {
                    $match[0] = \call_user_func(
                        $this->customDirectives[$match[1]],
                        $this->stripParentheses(static::Get($match, 3))
                    );
                }
            } elseif (\method_exists($this, $method = 'Compile' . \ucfirst($match[1]))) {
                $match[0] = $this->$method(static::Get($match, 3));
            } else {
                return $match[0];
            }
            return isset($match[3]) ? $match[0] : $match[0] . $match[2];
        };
        return \preg_replace_callback('/\B@(@?\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param \ArrayAccess|array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function Get($array, $key, $default = null)
    {
        $accesible = \is_array($array) || $array instanceof \ArrayAccess;
        if (!$accesible) {
            return static::value($default);
        }
        if (\is_null($key)) {
            return $array;
        }
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        foreach (\explode('.', $key) as $segment) {
            if ($accesible && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return static::value($default);
            }
        }
        return $array;
    }

    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function Value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param \ArrayAccess|array $array
     * @param string|int $key
     * @return bool
     */
    public static function Exists($array, $key)
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }
        return \array_key_exists($key, $array);
    }

    /**
     * Add new loop to the stack.
     *
     * @param array|\Countable $data
     * @return void
     */
    private function AddLoop($data)
    {
        $length = \is_array($data) || $data instanceof \Countable ? \count($data) : null;
        $parent = static::last($this->loopsStack);
        $this->loopsStack[] = [
            'index' => 0,
            'remaining' => isset($length) ? $length + 1 : null,
            'count' => $length,
            'first' => true,
            'last' => isset($length) ? $length == 1 : null,
            'depth' => \count($this->loopsStack) + 1,
            'parent' => $parent ? (object)$parent : null,
        ];
    }

    /**
     * Get an instance of the first loop in the stack.
     *
     * @return array|object
     */
    private function GetFirstLoop()
    {
        return ($last = static::last($this->loopsStack)) ? (object)$last : null;
    }

    /**
     * Increment the top loop's indices.
     *
     * @return void
     */
    private function IncrementLoopIndices()
    {
        $loop = &$this->loopsStack[count($this->loopsStack) - 1];
        $loop['index']++;
        $loop['first'] = $loop['index'] == 1;
        if (isset($loop['count'])) {
            $loop['remaining']--;
            $loop['last'] = $loop['index'] == $loop['count'];
        }
    }

    /**
     * Pop a loop from the top of the loop stack.
     *
     * @return void
     */
    private function PopLoop()
    {
        \array_pop($this->loopsStack);
    }

    /**
     * Compile the foreach statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function CompileForeach($expression)
    {
        //\preg_match('/\( *(.*) * as *([^\)]*)/', $expression, $matches);
        \preg_match('/\( *(.*) * as *([^)]*)/', $expression, $matches);
        $iteratee = \trim($matches[1]);
        $iteration = \trim($matches[2]);
        $initLoop = "\$__currentLoopData = {$iteratee}; \$this->AddLoop(\$__currentLoopData);";
        $iterateLoop = '$this->IncrementLoopIndices(); $loop = $this->GetFirstLoop();';
        return $this->PhpTag . "{$initLoop} foreach(\$__currentLoopData as {$iteration}): {$iterateLoop} ?>";
    }

    /**
     * Compile the end-for-each statements into valid PHP.
     *
     * @return string
     */
    protected function CompileEndforeach()
    {
        return $this->PhpTag . 'endforeach; $this->PopLoop(); $loop = $this->GetFirstLoop(); ?>';
    }

    protected function compileSet($expression)
    {
        $segments = \explode('=', \preg_replace("/[()\\\']/", '', $expression));
        $value = (\count($segments) >= 2) ? ' =@' . $segments[1] : '++';
        return $this->PhpTag . \trim($segments[0]) . $value . "; ?>";
    }

    /**
     * Compile the if statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function CompileIf($expression)
    {
        return $this->PhpTag . "if{$expression}: ?>";
    }

    /**
     * Compile the end-if statements into valid PHP.
     *
     * @return string
     */
    protected function CompileEndif()
    {
        return $this->PhpTag . 'endif; ?>';
    }

    /**
     * Compile the else statements into valid PHP.
     *
     * @return string
     */
    protected function CompileElse()
    {
        return $this->PhpTag . 'else: ?>';
    }

    /**
     * Compile the else-if statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileElseif($expression)
    {
        return $this->PhpTag . "elseif{$expression}: ?>";
    }

    /**
     * Compile the for statements into valid PHP.
     *
     * @param string $expression
     * @return string
     */
    protected function compileFor($expression)
    {
        return $this->PhpTag . "for{$expression}: ?>";
    }

    /**
     * Compile the end-for statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndfor()
    {
        return $this->PhpTag . 'endfor; ?>';
    }
}