<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthApiTest extends TestCase
{
    public function test_health_mengembalikan_200_saat_semua_komponen_sehat(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok');
    }
}