<?php
namespace b3nl\RESTScaffolding\Code\Line;

use b3nl\RESTScaffolding\Code\Line;

/**
 * Loads code lines for the given file.
 * @author b3nl
 * @package b3nl\RESTScaffolding
 * @subpackage Code\Line
 * @verion $id$
 */
class Factory
{
    /**
     * Creates Code lines out of the content.
     * @param string $content
     * @return Line[]
     */
    public function parseContent($content)
    {
        $lines = [];

        if ($content) {
            $tokens = token_get_all($content);

            $lines += $this->parseLines($tokens);
        } // if

        return array_values(array_filter($lines));
    }

    /**
     * Parses code lines out of the given file.
     * @param string $file
     * @return Line[]
     */
    public function parseFile($file)
    {
        return $this->parseContent(@ (string)file_get_contents($file));
    } // function

    /**
     * Parses a single line out of the given tokens. Removes the found token for the created line from the tokens array.
     * @param array $tokens
     * @return Line|null
     */
    protected function parseLine(array &$tokens)
    {
        $content = '';
        $return = null;
        $tokenIdent = null;

        while ($token = array_shift($tokens)) {
            $isSimpleString = !is_array($token);

            if ($isSimpleString && $token === '}') {
                break;
            }

            if ($tokenIdent === null) {
                // Every line starts with a token. As long as there is no token, whitespace, etc. skip it!
                if ($isSimpleString || in_array($token[0], [T_WHITESPACE])) {
                    continue;
                } // if

                $tokenIdent = $token[0];
            } // if

            if (!$isSimpleString) {
                $token = $token[1];
            } // if

            // Ignore line ends.
            if (!in_array($token, [';', '}', '{'])) {
                $content .= $token;
            } // if

            // Change or break depth
            $isComment = in_array($tokenIdent, array(T_DOC_COMMENT, T_COMMENT), true);
            if ((in_array($token, [';', '{'])) || $isComment) {
                $return = new Line($content, $tokenIdent);

                // Dive into depth.
                if ($token === '{') {
                    $return->setChildLines($this->parseLines($tokens));
                } // if

                break;
            } // if
        } // while

        return $return;
    } // function

    /**
     * Parses the lines out of the given tokens.
     * @param array $tokens
     * @return Line[]
     */
    protected function parseLines(array &$tokens)
    {
        $lines = [];

        while ($token = array_shift($tokens)) {
            // Jump to the next line in the same depth.
            if (is_array($token) && $token === ';') {
                continue;
            } // if

            $line = $this->parseLine($tokens);

            if (!$line) {
                break;
            } // if

            $lines[] = $line;
        } // while

        return array_values(array_filter($lines));
    } // function
}
