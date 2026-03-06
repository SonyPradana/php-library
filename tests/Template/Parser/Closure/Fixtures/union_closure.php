<?php

namespace Tests\Template\Parser\Closure;

return static function (UnionA|UnionB $param): UnionC|UnionD {
    return new UnionC();
};
