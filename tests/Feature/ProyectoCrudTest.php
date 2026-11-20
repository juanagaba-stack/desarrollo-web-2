<?php

namespace Tests\Feature;

use App\Models\Correccion;
use App\Models\IntegranteProyecto;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProyectoCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = User::factory()->create();
    }

    private function crearProyecto(array $extra = []): Proyecto
    {
        return Proyecto::create(array_merge([
            'codigo_radicado' => 'PRJ-2026-001',
            'titulo' => 'Sistema de prueba',
            'asunto_linea_investigacion' => 'Ingeniería de Software',
            'resumen' => 'Resumen de prueba con más de veinte caracteres.',
            'estado' => 'EN_REVISION',
            'fecha_radicacion' => '2026-02-10',
            'usuario_creador_id' => $this->usuario->id,
        ], $extra));
    }

    private function datosProyecto(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Detección de plagas con visión por computador',
            'asunto_linea_investigacion' => 'Machine Learning',
            'resumen' => 'Modelo de clasificación de imágenes para cultivos de maíz.',
            'fecha_radicacion' => '2026-03-01',
        ], $extra);
    }

    public function test_invitado_no_puede_entrar_al_crud(): void
    {
        $this->get('/proyectos/create')->assertRedirect('/login');
        $this->post('/proyectos', $this->datosProyecto())->assertRedirect('/login');
    }

    public function test_dashboard_lista_los_proyectos(): void
    {
        $this->crearProyecto();

        $this->actingAs($this->usuario)->get('/dashboard')
            ->assertOk()
            ->assertSee('PRJ-2026-001')
            ->assertSee('Sistema de prueba');
    }

    public function test_crear_proyecto_genera_codigo_y_asigna_creador(): void
    {
        $respuesta = $this->actingAs($this->usuario)->post('/proyectos', $this->datosProyecto());

        $proyecto = Proyecto::firstOrFail();
        $respuesta->assertRedirect(route('proyectos.show', $proyecto));

        $this->assertMatchesRegularExpression('/^PRJ-\d{4}-001$/', $proyecto->codigo_radicado);
        $this->assertSame($this->usuario->id, $proyecto->usuario_creador_id);
        $this->assertSame('EN_REVISION', $proyecto->fresh()->estado);
    }

    public function test_el_consecutivo_no_se_repite_despues_de_eliminar(): void
    {
        $anio = now()->year;
        $this->crearProyecto(['codigo_radicado' => "PRJ-{$anio}-001"]);
        $segundo = $this->crearProyecto(['codigo_radicado' => "PRJ-{$anio}-002"]);
        $segundo->delete();

        $this->assertSame("PRJ-{$anio}-002", Proyecto::siguienteCodigo());
    }

    public function test_crear_proyecto_valida_campos_obligatorios(): void
    {
        $this->actingAs($this->usuario)
            ->post('/proyectos', ['titulo' => '', 'resumen' => 'corto'])
            ->assertSessionHasErrors(['titulo', 'asunto_linea_investigacion', 'resumen', 'fecha_radicacion']);

        $this->assertSame(0, Proyecto::count());
    }

    public function test_ver_detalle_del_proyecto(): void
    {
        $proyecto = $this->crearProyecto();
        Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Ajustar la metodología propuesta',
            'fecha_limite' => '2026-03-15',
        ]);
        IntegranteProyecto::create([
            'proyecto_id' => $proyecto->id,
            'user_id' => $this->usuario->id,
            'rol_en_proyecto' => 'AUTOR_ESTUDIANTE',
        ]);

        $this->actingAs($this->usuario)->get(route('proyectos.show', $proyecto))
            ->assertOk()
            ->assertSee('Ajustar la metodología propuesta')
            ->assertSee($this->usuario->name)
            ->assertSee('15/03/2026');
    }

    public function test_formulario_de_edicion_carga_los_datos(): void
    {
        $proyecto = $this->crearProyecto();

        $this->actingAs($this->usuario)->get(route('proyectos.edit', $proyecto))
            ->assertOk()
            ->assertSee('Sistema de prueba')
            ->assertSee('2026-02-10');
    }

    public function test_actualizar_proyecto(): void
    {
        $proyecto = $this->crearProyecto();

        $this->actingAs($this->usuario)
            ->put(route('proyectos.update', $proyecto), $this->datosProyecto(['estado' => 'APROBADO']))
            ->assertRedirect(route('proyectos.show', $proyecto));

        $proyecto->refresh();
        $this->assertSame('Detección de plagas con visión por computador', $proyecto->titulo);
        $this->assertSame('APROBADO', $proyecto->estado);
        // el código de radicado no se toca al editar
        $this->assertSame('PRJ-2026-001', $proyecto->codigo_radicado);
    }

    public function test_no_acepta_un_estado_invalido(): void
    {
        $proyecto = $this->crearProyecto();

        $this->actingAs($this->usuario)
            ->put(route('proyectos.update', $proyecto), $this->datosProyecto(['estado' => 'INVENTADO']))
            ->assertSessionHasErrors('estado');
    }

    public function test_eliminar_proyecto_borra_sus_relaciones(): void
    {
        $proyecto = $this->crearProyecto();
        Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Observación de prueba',
            'fecha_limite' => '2026-03-15',
        ]);
        IntegranteProyecto::create([
            'proyecto_id' => $proyecto->id,
            'user_id' => $this->usuario->id,
            'rol_en_proyecto' => 'DIRECTOR_TUTOR',
        ]);

        $this->actingAs($this->usuario)
            ->delete(route('proyectos.destroy', $proyecto))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, Proyecto::count());
        $this->assertSame(0, Correccion::count());
        $this->assertSame(0, IntegranteProyecto::count());
    }

    public function test_registrar_observacion(): void
    {
        $proyecto = $this->crearProyecto();

        $this->actingAs($this->usuario)
            ->post(route('proyectos.correcciones.store', $proyecto), [
                'descripcion_observacion' => 'Incluir el marco teórico completo',
                'fecha_limite' => now()->addWeek()->format('Y-m-d'),
            ])
            ->assertRedirect(route('proyectos.show', $proyecto));

        $correccion = $proyecto->correcciones()->first();
        $this->assertSame('PENDIENTE', $correccion->fresh()->estado);
    }

    public function test_observacion_nueva_no_acepta_fecha_pasada(): void
    {
        $proyecto = $this->crearProyecto();

        $this->actingAs($this->usuario)
            ->post(route('proyectos.correcciones.store', $proyecto), [
                'descripcion_observacion' => 'Incluir el marco teórico completo',
                'fecha_limite' => '2020-01-01',
            ])
            ->assertSessionHasErrorsIn('correccion', 'fecha_limite');
    }

    public function test_editar_y_resolver_observacion(): void
    {
        $proyecto = $this->crearProyecto();
        $correccion = Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Texto original de la observación',
            'fecha_limite' => '2026-03-15',
        ]);

        $this->actingAs($this->usuario)->get(route('correcciones.edit', $correccion))->assertOk();

        $this->actingAs($this->usuario)
            ->put(route('correcciones.update', $correccion), [
                'descripcion_observacion' => 'Texto corregido de la observación',
                'fecha_limite' => '2026-03-15',
                'estado' => 'RESUELTO',
            ])
            ->assertRedirect(route('proyectos.show', $proyecto));

        $correccion->refresh();
        $this->assertSame('RESUELTO', $correccion->estado);
        $this->assertSame('Texto corregido de la observación', $correccion->descripcion_observacion);
    }

    public function test_eliminar_observacion(): void
    {
        $proyecto = $this->crearProyecto();
        $correccion = Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Observación que se va a borrar',
            'fecha_limite' => '2026-03-15',
        ]);

        $this->actingAs($this->usuario)
            ->delete(route('correcciones.destroy', $correccion))
            ->assertRedirect(route('proyectos.show', $proyecto));

        $this->assertSame(0, Correccion::count());
    }

    public function test_agregar_y_quitar_integrante(): void
    {
        $proyecto = $this->crearProyecto();
        $tutor = User::factory()->create();

        $this->actingAs($this->usuario)
            ->post(route('integrantes.store', $proyecto), [
                'user_id' => $tutor->id,
                'rol_en_proyecto' => 'DIRECTOR_TUTOR',
            ])
            ->assertRedirect(route('proyectos.show', $proyecto));

        $integrante = IntegranteProyecto::firstOrFail();
        $this->assertSame($tutor->id, $integrante->user_id);

        $this->actingAs($this->usuario)
            ->delete(route('integrantes.destroy', $integrante))
            ->assertRedirect(route('proyectos.show', $proyecto));

        $this->assertSame(0, IntegranteProyecto::count());
    }

    public function test_no_se_repite_un_integrante_en_el_mismo_proyecto(): void
    {
        $proyecto = $this->crearProyecto();
        $datos = ['user_id' => $this->usuario->id, 'rol_en_proyecto' => 'AUTOR_ESTUDIANTE'];

        $this->actingAs($this->usuario)->post(route('integrantes.store', $proyecto), $datos);
        $this->actingAs($this->usuario)->post(route('integrantes.store', $proyecto), $datos)
            ->assertSessionHasErrorsIn('integrante', 'user_id');

        $this->assertSame(1, IntegranteProyecto::count());
    }

    public function test_la_pagina_de_perfil_sigue_funcionando(): void
    {
        $this->actingAs($this->usuario)->get('/profile')->assertOk();
    }

    public function test_porcentaje_de_avance_segun_estado_y_observaciones(): void
    {
        $proyecto = $this->crearProyecto();
        $this->assertSame(30, $proyecto->porcentaje_avance);

        $proyecto->update(['estado' => 'CON_OBSERVACIONES']);
        foreach (['2026-03-15', '2026-03-16', '2026-03-17', '2026-03-18'] as $fecha) {
            Correccion::create([
                'proyecto_id' => $proyecto->id,
                'descripcion_observacion' => 'Observación de prueba',
                'fecha_limite' => $fecha,
            ]);
        }
        $this->assertSame(30, $proyecto->fresh()->porcentaje_avance);

        Correccion::query()->limit(2)->update(['estado' => 'RESUELTO']);
        $this->assertSame(60, $proyecto->fresh()->porcentaje_avance);

        Correccion::query()->update(['estado' => 'RESUELTO']);
        $this->assertSame(90, $proyecto->fresh()->porcentaje_avance);

        $proyecto->update(['estado' => 'APROBADO']);
        $this->assertSame(100, $proyecto->fresh()->porcentaje_avance);
    }

    public function test_alternar_observacion_devuelve_el_avance_actualizado(): void
    {
        $proyecto = $this->crearProyecto(['estado' => 'CON_OBSERVACIONES']);
        $primera = Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Primera observación',
            'fecha_limite' => '2026-03-15',
        ]);
        Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Segunda observación',
            'fecha_limite' => '2026-03-16',
        ]);

        $this->actingAs($this->usuario)
            ->patchJson(route('correcciones.alternar', $primera))
            ->assertOk()
            ->assertJson(['estado' => 'RESUELTO', 'porcentaje' => 60, 'resueltas' => 1, 'total' => 2]);

        // un segundo clic la vuelve a dejar pendiente
        $this->actingAs($this->usuario)
            ->patchJson(route('correcciones.alternar', $primera))
            ->assertOk()
            ->assertJson(['estado' => 'PENDIENTE', 'porcentaje' => 30, 'resueltas' => 0]);
    }

    public function test_registrar_observacion_cambia_el_estado_del_proyecto(): void
    {
        $proyecto = $this->crearProyecto(['estado' => 'EN_REVISION']);

        $this->actingAs($this->usuario)->post(route('proyectos.correcciones.store', $proyecto), [
            'descripcion_observacion' => 'Falta el marco teórico',
            'fecha_limite' => now()->addWeek()->format('Y-m-d'),
        ]);

        $this->assertSame('CON_OBSERVACIONES', $proyecto->fresh()->estado);
    }

    public function test_aprobar_desde_el_boton_de_sugerencia(): void
    {
        $proyecto = $this->crearProyecto(['estado' => 'CON_OBSERVACIONES']);

        $this->actingAs($this->usuario)
            ->patchJson(route('proyectos.estado', $proyecto), ['estado' => 'APROBADO'])
            ->assertOk()
            ->assertJson(['estado' => 'APROBADO']);

        $this->actingAs($this->usuario)
            ->patchJson(route('proyectos.estado', $proyecto), ['estado' => 'XYZ'])
            ->assertStatus(422);

        $this->assertSame('APROBADO', $proyecto->fresh()->estado);
    }

    public function test_semaforo_de_plazos(): void
    {
        $proyecto = $this->crearProyecto();
        $crear = fn ($dias, $estado = 'PENDIENTE') => Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Observación de prueba',
            'fecha_limite' => today()->addDays($dias),
            'estado' => $estado,
        ]);

        $this->assertSame('rojo', $crear(-2)->semaforo);
        $this->assertSame('amarillo', $crear(0)->semaforo);
        $this->assertSame('amarillo', $crear(3)->semaforo);
        $this->assertSame('verde', $crear(10)->semaforo);
        $this->assertSame('verde', $crear(-5, 'RESUELTO')->semaforo);
        $this->assertSame('Vencida hace 2 días', $crear(-2)->texto_plazo);
        $this->assertSame('Vence hoy', $crear(0)->texto_plazo);
    }

    public function test_dashboard_muestra_alertas_y_avance(): void
    {
        $proyecto = $this->crearProyecto(['estado' => 'CON_OBSERVACIONES']);
        Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Entregar el cronograma ajustado',
            'fecha_limite' => today()->subDays(2),
        ]);
        Correccion::create([
            'proyecto_id' => $proyecto->id,
            'descripcion_observacion' => 'Observación con plazo largo',
            'fecha_limite' => today()->addDays(30),
        ]);

        $this->actingAs($this->usuario)->get('/dashboard')
            ->assertOk()
            ->assertSee('Entregar el cronograma ajustado')
            ->assertSee('Vencida hace 2 días')
            ->assertDontSee('Observación con plazo largo')
            ->assertSee('30%');
    }
}
