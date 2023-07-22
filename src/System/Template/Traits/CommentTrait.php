<?php

declare(strict_types=1);

namespace System\Template\Traits;

/**
 * Trait for adding comment.
 */
trait CommentTrait
{
    /** @var string[] */
    private $comments = [];

    public function addComment(?string $comment): self
    {
        $this->comments[] = $comment ?? '';

        return $this;
    }

    public function addLineComment(): self
    {
        return $this->addComment(null);
    }

    public function addParamComment(string $datatype, string $name, string $description): self
    {
        $name        = $name == '' ? $name : ' ' . $name;
        $description = $description == '' ? $description : ' ' . $description;

        $this->comments[] = "@param $datatype$name$description";

        return $this;
    }

    public function addVaribaleComment(string $datatype, string $name = ''): self
    {
        $name = $name == '' ? $name : ' ' . $name;

        $this->comments[] = "@var $datatype$name";

        return $this;
    }

    public function addReturnComment(string $datatype, string $name = '', string $description = ''): self
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
