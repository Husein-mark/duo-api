<?php

test('halaman dokumentasi swagger ui dapat diakses dengan sukses', function () {
    $response = $this->get('/api/documentation');

    $response->assertStatus(200);
});

test('file spesifikasi openapi json dapat diakses di endpoint docs', function () {
    $response = $this->get('/docs');

    $response->assertStatus(200);
});
