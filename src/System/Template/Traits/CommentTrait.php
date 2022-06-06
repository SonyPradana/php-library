<?php

namespace System\Template\Traits;

/**
 * Trait for adding comment.
 */
trait CommentTrait
{
    private $comments = [];

    public function addComment(?string $comment)
    {
        $this->comments[] = $comment ?? '';

        return $this;
    }

    public function addLineComment()
    {
        return $this->addComment(null);
    }

    public function addParamComment(string $datatype, string $name, string $description)
    {
        $name        = $name == '' ? $name : ' ' . $name;
        $description = $description == '' ? $description : ' ' . $description;

        $this->comments[] = "@param $datatype$name$description";

        return $this;
    }

    public function addVaribaleComment(string $datatype, string $name = '')
    {
        $name = $name == '' ? $name : ' ' . $name;

        $this->comments[] = "@var $datatype$name";

        return $this;
    }

    public function addReturnComment(string $datatype, string $name = '', string $description = '')
    {
        $name        = $name == '' ? $name : ' ' . $name;
        $description = $description == '' ? $description : ' ' . $description;

        $this->comments[] = "@return $datatype$name$description";

        return $this;
    }

    public function commentTemplate(): string
    {
        return '/** {{body}} */';
    }

    public function generateComment(int $tab_size = 0, string $tab_indent = "\t"): string
    {
        $template      = $this->commentTemplate();
        $count_commnet = count($this->comments);
        $end_line      = '';
        $tab_dept      = str_repeat($tab_indent, $tab_size);

        if ($count_commnet > 0) {
            if ($count_commnet > 1) {
                array_unshift($this->comments, '');
                $end_line = "\n$tab_dept";
            }

            $comment = implode("\n$tab_dept * ", $this->comments) . $end_line;

            return str_replace('{{body}}', $comment, $template);
        }

        // return empty if comment not avilabe
        return '';
    }
}
