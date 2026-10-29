<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_page_redirects_to_the_online_catalogue(): void
    {
        $this->get('/')->assertRedirect(route('shop.index'));
    }
}
