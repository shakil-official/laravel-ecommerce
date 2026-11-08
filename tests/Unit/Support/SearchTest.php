<?php

namespace Tests\Unit\Support;

use App\Support\Search;
use Tests\TestCase;

class SearchTest extends TestCase
{
    public function test_like_wraps_the_term_in_wildcards(): void
    {
        $this->assertSame('%phone%', Search::like('phone'));
    }

    public function test_like_escapes_wildcards_and_the_escape_character(): void
    {
        $this->assertSame('%50!%!_off!!%', Search::like('50%_off!'));
    }
}
