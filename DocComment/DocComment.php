<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 07.11.14 21:57
 */

namespace Solve\DocComment;


use Solve\Storage\ArrayStorage;

class DocComment {

    private $_description = '';
    private $_annotations;

    public function __construct() {
        $this->_annotations = new ArrayStorage();
    }

    /**
     * @param $source
     * @return DocComment
     */
    public static function parseFromString($source) {
        $comment = new DocComment();
        if (substr($source, 0, 3) !== '/**') {
            return $comment;
        }

        $source = explode("\n", preg_replace(array('~(^\s*/\*\*\s*|\s*\*/\s*$)~',
                           '~^\h*\*?\h?~m',
                           '~\h+$~'
        ), '', $source));
        $commentDescription = '';
        $isDescription = true;
        foreach ($source as $line) {
            if ($line && $line[0] == '@') {
                $isDescription = false;
                $nameEndIndex = strpos($line, ' ');
                $description = '';
                if ($nameEndIndex !== false) {
                    $name = mb_substr($line, 1, $nameEndIndex-1);
                    $description = trim(mb_substr($line, $nameEndIndex));
                } else {
                    $name = mb_substr($line, 1);
                }
                $comment->addAnnotation($name, $description);
            } else {
                if ($isDescription) {
                    $commentDescription .= $line . "\n";
                }
            }
        }
        $comment->setDescription(trim($commentDescription));
        return $comment;
    }

    public function addAnnotation($key, $description) {
        $current = $this->_annotations->getDeepValue($key, array());
        $current[] = $description;
        $this->_annotations->setDeepValue($key, $current);
    }

    public function getAnnotations($key = null) {
        return $this->_annotations->getDeepValue($key, array());
    }

    public function getAnnotationsAsString($key = null) {
        return implode("\n", $this->getAnnotations($key));
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->_description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->_description = $description;
    }


}