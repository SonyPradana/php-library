<?php

namespace Tests\Template\Parser\Closure;

return static function (IntersectionA&IntersectionB $param): IntersectionA&IntersectionB {
    return new class implements IntersectionA, IntersectionB {};
};
