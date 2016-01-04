<?php
namespace b3nl\RESTScaffolding\Contracts;

use b3nl\RESTScaffolding\Code\Line;

/**
 * Basic-Interface for our code traversing.
 * @author b3nl
 * @category Contracts
 * @package b3nl\RESTScaffolding
 * @subpackage Contracts
 * @version $id$
 */
interface CodeTraversing
{
    /**
     * Adds a new line after the other line.
     * @param Line $new
     * @param Line $source
     * @return CodeTraversing
     */
    public function addAfter(Line $new, Line $source); // function

    /**
     * Adds a new line after the other line.
     * @param Line $new
     * @param Line $source
     * @return CodeTraversing
     */
    public function addBefore(Line $new, Line $source); // function

    /**
     * Adds a line to this file.
     * @param Line $line
     * @return Line
     */
    public function appendLine(Line $line); // function

    /**
     * Finds a matching file.
     * @param array $search The key is a sprintf expression, which is parsed to a regex with the array value.
     * @param int $limit
     * @param int $nestingLevel
     * @return Line[]|Line
     */
    public function findLine($search, $limit = 0, $nestingLevel = -1); // function

    /**
     * Prepends the given line.
     * @param Line $line
     * @return Line The line itself.
     */
    public function prependLine(Line $line); // function

    /**
     * Sets the content of this code collection.
     * @param string $content
     * @return CodeTraversing
     */
    public function setContent($content); // function
}
