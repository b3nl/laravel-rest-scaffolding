<?php
namespace b3nl\RESTScaffolding\Code;

use ArrayIterator;
use b3nl\RESTScaffolding\File;
use RecursiveIterator;

/**
 * Enables a tree structure for this given code file.
 * @author b3nl
 * @package b3nl\RESTScaffolding
 * @subpackage Code
 * @version $id$
 */
class Line implements RecursiveIterator
{
    /**
     * The ChildLines of this line, for example the content of an if check.
     * @var Line[]
     */
    protected $ChildLines = [];

    /**
     * The content of this line.
     * @var string
     */
    protected $content = '';

    /**
     * The file of this line.
     * @var File|void
     */
    protected $file = null;

    /**
     * Caches the iterator instance.
     * @var null|ArrayIterator
     */
    protected $iterator = null;

    /**
     * The nesting level of this line in the actual file.
     * @var int
     */
    protected $nestingLevel = 1;

    /**
     * The parent code line if there is one.
     * @var Line|null
     */
    protected $parent = null;

    /**
     * The token from this line: <http://php.net/manual/de/tokens.php>
     * @var int
     */
    protected $token = null;

    /**
     * Line constructor.
     * @param string $content
     * @param int $token
     */
    public function __construct($content, $token = null)
    {
        $this
            ->setContent($content)
            ->setToken($token);
    } // function

    /**
     * Returns the rendered line.
     * @return string
     */
    public function __toString()
    {
        $content = $this->getContent();

        if (!$ChildLines = $this->getChildLines()) {
            if (!in_array($this->getToken(), [T_COMMENT, T_DOC_COMMENT], true)) {
                $content .= ";";
            } // if
        } else {
            $inlineCall = ($startingParaCount = substr_count($content, '('))
                ? $startingParaCount !== substr_count($content, ')')
                : false;

            $content .= "{\n" . implode("\n", $ChildLines) . '}' . ($inlineCall ? ');' : '') . "\n";
        } // else

        return $content;
    } // function

    /**
     * Adds a child line.
     * @param Line $line
     * @return Line The child line.
     */
    public function appendLine(Line $line)
    {
        $this->ChildLines[] = $line
            ->setNestingLevel($this->getNestingLevel() + 1)
            ->setParent($this);

        return $line;
    } // function

    /**
     * Returns the current element.
     * @return Line|void
     */
    public function current()
    {
        return $this->getIterator()->current();
    } // function

    /**
     * Returns the ChildLines of this line.
     * @return Line[]
     */
    public function getChildLines()
    {
        return $this->ChildLines;
    } // function

    /**
     * Returns an iterator for the children of the latest element.
     * @return ArrayIterator
     */
    public function getChildren()
    {
        return $this->valid() ? $this->current() : null;
    } // function

    /**
     * Returns an iterator for loop thru the lines.
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if (!$this->iterator) {
            $this->iterator = new ArrayIterator($this->getChildLines());
        } // if

        return $this->iterator;
    } // function

    /**
     * Returns the content for this line.
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Returns the file of this file or null.
     * @return File|void
     */
    public function getFile()
    {
        return $this->file;
    } // function

    /**
     * Returns the nesting level for this line.
     * @return int
     */
    public function getNestingLevel()
    {
        return $this->nestingLevel;
    } // function

    /**
     * Returns the parent of this code line.
     * @return Line|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the token of this line.
     * @return int
     */
    public function getToken()
    {
        return $this->token;
    } // function

    /**
     * Returns true if this line has ChildLines.
     * @return bool
     */
    public function hasChildLines()
    {
        return (bool) $this->ChildLines;
    } // function

    /**
     * Returns true if this element has a child.
     * @return bool
     */
    public function hasChildren()
    {
        return $this->valid() ? $this->current()->hasChildLines() : false;
    } // function

    /**
     * Returns the iterated key.
     * @return int|void
     */
    public function key()
    {
        return $this->getIterator()->key();
    } // function

    /**
     * Returns the next element.
     * @return void|Line
     */
    public function next()
    {
        return $this->getIterator()->next();
    } // function

    /**
     * Prepends a line.
     * @param Line $line
     * @return Line The line itself.
     */
    public function prependLine(Line $line)
    {
        array_unshift(
            $this->ChildLines,
            $line
            ->setNestingLevel($this->getNestingLevel() + 1)
            ->setParent($this)
        );

        return $line;
    } // function

    /**
     * Resets the iterator.
     * @return void.
     */
    public function rewind()
    {
        $this->iterator = null;
    } // function

    /**
     * Sets the ChildLines of this line.
     * @param Line []
     * @return Line
     */
    public function setChildLines(array $ChildLines)
    {
        $this->ChildLines = [];

        foreach ($ChildLines as $line) {
            $this->appendLine($line);
        } // foreach

        return $this;
    } // function

    /**
     * The content itself.
     * @param string $content
     * @return Line
     */
    public function setContent($content)
    {
        $this->content = trim($content, ';{');

        return $this;
    } // function

    /**
     * Sets the file of this line.
     * @param File $file
     * @return $this
     */
    public function setFile(File $file)
    {
        $this->file = $file;

        return $this;
    } // function

    /**
     * The nesting level of this line in the actual file.
     * @param int $nestingLevel
     * @return Line
     */
    public function setNestingLevel($nestingLevel)
    {
        $this->nestingLevel = $nestingLevel;

        return $this;
    } // function

    /**
     * Sets the parent of this code line.
     * @param Line|null $parent
     * @return Line
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    } // function

    /**
     * Sets the token of this line.
     * @param int $token
     * @return Line
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    } // function

    /**
     * Is the actual entry valid?
     * @return bool
     */
    public function valid()
    {
        return $this->getIterator()->valid();
    } // function
}
