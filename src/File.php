<?php
namespace b3nl\RESTScaffolding;

use ArrayIterator;
use PHP_CodeSniffer_CLI;
use RecursiveIterator;
use b3nl\RESTScaffolding\Code\Line;
use b3nl\RESTScaffolding\Code\Line\Factory;
use b3nl\RESTScaffolding\Contracts\CodeTraversing;

/**
 * Class for generating and finding code.
 * @author b3nl
 * @category app
 * @package b3nl\RESTScaffolding
 * @version $id$
 */
class File implements RecursiveIterator, CodeTraversing
{
    /**
     * Caches the iterator instance.
     * @var null|ArrayIterator
     */
    protected $iterator = null;

    /**
     * The cached lines of this file.
     * @var Line[]
     */
    protected $lines = [];

    /**
     * The path to this file.
     * @var string
     */
    protected $filePath = '';

    /**
     * File constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->setFilePath($filePath);
    } // function

    /**
     * Returns the file content.
     * @return string
     */
    public function __toString()
    {
        return "<?php\n" . implode("\n", $this->getLines());
    } // function

    /**
     * Adds a new line after the other line.
     * @param Line $new
     * @param Line $source
     * @return CodeTraversing
     */
    public function addAfter(Line $new, Line $source) {
        $sourceIndex = array_search($source, $lines = $this->getLines());

        if ($sourceIndex === false) {
            $this->appendLine($new);
        } else {
            array_splice($lines, $sourceIndex + 1, 0, [$new]);

            $this->setLines($lines);
        } // else

        return $this;
    } // function

    /**
     * Adds a new line after the other line.
     * @param Line $new
     * @param Line $source
     * @return CodeTraversing
     */
    public function addBefore(Line $new, Line $source) {
        $sourceIndex = array_search($source, $lines = $this->getLines()); // TODO Check if new Line.

        if (!$sourceIndex) {
            $this->prependLine($new);
        } else {
            array_splice($lines, $sourceIndex, 0, [$new]);

            $this->setLines($lines);
        }

        return $this;
    } // function

    /**
     * Adds a line to this file.
     * @param Line $line
     * @return Line
     */
    public function appendLine(Line $line)
    {
        $this->lines[] = $line;

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
     * Finds a matching file.
     * @param array $search The key is a sprintf expression, which is parsed to a regex with the array value.
     * @param int $limit
     * @param int $nestingLevel
     * @return array
     */
    public function findLine($search, $limit = 0, $nestingLevel = -1)
    {
        $matchCount = 0;
        $return = [];

        if (is_array($search)) {
            $searchWords = current($search) ?: [key($search)];

            $regex = call_user_func_array(
                'sprintf',
                array_merge(
                    [str_replace('\\?', '?', '/' . preg_quote(key($search), '/') . '/')],
                    array_map(
                        function ($searchWord) {
                            return '(' . preg_quote($searchWord, '/') . ')';
                        },
                        $searchWords
                    )
                )
            );
        } else {
            $regex = $search;
        }

        $iterator = new \RecursiveCallbackFilterIterator(
            $this,
            function (Line $current) use ($regex, $nestingLevel) {
                // Check the content against the given regex.
                $return = (bool)preg_match($regex, $current);

                /*
                 * Match only the required nesting level if given or allow the parent traversing. This iterator does not
                 * find childs, if the parent did not match!
                 */
                return $nestingLevel === -1 ||
                    ($return && (($current->hasChildLines()) || ($current->getNestingLevel() === $nestingLevel)));
            }
        );

        // The RecursiveCallbackFilterIterator did not work correctly. it ignored the childs.
        foreach (new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST) as $line) {
            if ($nestingLevel === -1 || $line->getNestingLevel() === $nestingLevel) {
                // Check the content against the given regex.
                $matched = (bool)preg_match($regex, $line->getContent());

                if ($matched) {
                    ++$matchCount;
                    $return[] = $line;
                }

                if ($limit && $matchCount >= $limit) {
                    break;
                } // if
            } // if
        } // foreach

        if ($limit === 1 && count($return) === $limit) {
            $return = reset($return);
        } // if

        return $return;
    } // function

    /**
     * Returns an iterator for the children of the latest element.
     * @return RecursiveIterator
     */
    public function getChildren()
    {
        return $this->current();
    } // function

    /**
     * Returns an iterator for loop thru the lines.
     * @return ArrayIterator
     */
    public function getIterator()
    {
        if (!$this->iterator) {
            $this->iterator = new ArrayIterator($this->getLines());
        } // if

        return $this->iterator;
    } // function

    /**
     * Returns the code lines for this file.
     * @return Line[]
     */
    public function getLines()
    {
        if (!$this->lines && $this->getFilePath()) {
            $this->loadLines();
        } // if

        return $this->lines;
    } // function

    /**
     * Returns the path to this file.
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    } // function

    /**
     * Returns true if the latest element has a child.
     * @return bool
     */
    public function hasChildren()
    {
        return $this->valid() ? $this->current()->hasChildLines() : false;
    } // function

    /**
     * Loads the codes lines for this file.
     * @return File
     */
    protected function loadLines()
    {
        /** @var Factory $lineFactory */
        $lineFactory = app(Factory::class);
        $this->setLines($lineFactory->parseFile($this->getFilePath()));

        return $this;
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
     * Prepends the given line.
     * @param Line $line
     * @return Line The line itself.
     */
    public function prependLine(Line $line)
    {
        array_unshift($this->lines, $line);

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
     * Saves the codes lines of this file and formats it.
     * @return bool
     */
    public function save()
    {
        $saved = (bool)file_put_contents($path = $this->getFilePath(), $this);

        if ($saved) {
            ob_start();
            $cli = new PHP_CodeSniffer_CLI();
            $cli->process([
                'files' => [$path],
                'reports' => ['cbf' => null],
                'phpcbf-suffix' => '',
                'standard' => 'PSR2',
                'verbosity' => 0
            ]);
            ob_end_clean();
        } // if

        return $saved;
    } // function

    /**
     * Sets the content of this code collection.
     * @param string $content
     * @return CodeTraversing
     */
    public function setContent($content) {
        /** @var Factory $lineFactory */
        $lineFactory = app(Factory::class);
        $this->setLines($lineFactory->parseContent($content));

        return $this;
    } // function

    /**
     * Sets the lines array for this file.
     * @param Line[] $lines
     * @return File
     */
    public function setLines(array $lines)
    {
        array_walk($lines, function(Line $line) {
           $line->setFile($this);
        });

        $this->lines = $lines;

        return $this;
    } // function

    /**
     * Sets the file path for this file.
     * @param string $filePath
     * @return File
     */
    protected function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        $this->loadLines();

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
