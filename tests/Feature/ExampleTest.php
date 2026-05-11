<?php

namespace Tests\Feature;

use App\Models\Cartera;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_loads_for_authenticated_session(): void
    {
        $response = $this
            ->withSession(['usuario' => 'impulsego'])
            ->get('/');

        $response->assertOk();
    }

    public function test_unified_operational_pages_load_for_authenticated_session(): void
    {
        Cartera::query()->firstOrCreate([
            'slug' => 'propia12',
        ], [
            'nombre' => 'Propia 1 y 2',
            'activa' => true,
            'orden' => 10,
        ]);

        $this->withSession(['usuario' => 'impulsego'])
            ->get('/pagos')
            ->assertOk()
            ->assertSee('Modulo unico de pagos');

        $this->withSession(['usuario' => 'impulsego'])
            ->get('/gestiones')
            ->assertOk()
            ->assertSee('Gestiones');

        $this->withSession(['usuario' => 'impulsego'])
            ->get('/reportes/pagos')
            ->assertOk()
            ->assertSee('Reporte unico de pagos');

        $this->withSession(['usuario' => 'impulsego'])
            ->get('/reportes/gestiones')
            ->assertOk()
            ->assertSee('Reporte unico de gestiones');

        $this->withSession(['usuario' => 'impulsego'])
            ->get('/configuracion/carteras')
            ->assertOk()
            ->assertSee('Carteras registradas');
    }
}
