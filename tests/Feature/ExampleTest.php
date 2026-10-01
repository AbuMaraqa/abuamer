<?php

test('the application redirects the root URL to the Arabic home page', function () {
    $this->useRoutingLocale(null);

    $response = $this->get('/');

    $response->assertRedirect(url('ar'));
});
