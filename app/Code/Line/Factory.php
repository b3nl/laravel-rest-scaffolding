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
    public function parseFile($file)
    {
        $fileContent = @ (string)file_get_contents($file);
        $lines = [];

        if ($fileContent) {
            $tokens = token_get_all($fileContent);

            $lines += $this->parseLines($tokens);
        } // if

        return array_values(array_filter($lines));
    } // function

    protected function parseLine(array &$tokens)
    {
        $content = '';
        $isComment = false;
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
            if ((in_array($token, [';', '{'])) || ($isComment = $tokenIdent === T_COMMENT)) {
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
