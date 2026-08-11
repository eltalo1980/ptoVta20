<?php
// filepath: c:\laragon\www\tdm90\tests\Feature\GeneraLlaveTest.php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GeneraLlaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->limpiarDatosPrueba();
        $this->configurarDatosBase();
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosPrueba();
        parent::tearDown();
    }

    /** @test */
    public function puede_generar_llave_normal_con_grupos_completos()
    {
        // Arrange
        $user = User::factory()->create(['nivel' => 100]);
        $this->actingAs($user);

        $this->configurarParticipantesCompletos();

        // Act
        $response = $this->get('/TorneoEjecucionLlave/accion=|GeneraLlave|parametros=id_torneo|999|idCategoria|1|LlaveNormal');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('TorneoEjecucionLlave.index');
        
        // Verificar que se crearon partidos
        $partidosCreados = DB::table('torneo_fase_llave')
            ->where('idTorneo', 999)
            ->where('id_categoria', 1)
            ->count();
        
        $this->assertGreaterThan(0, $partidosCreados);
        
        // Verificar mensaje de éxito
        $response->assertViewHas('Mensaje');
        $mensaje = $response->viewData('Mensaje');
        $this->assertStringContainsString('Generada Correctamente', $mensaje);
    }

    /** @test */
    public function no_puede_generar_llave_con_grupos_incompletos()
    {
        // Arrange
        $user = User::factory()->create(['nivel' => 100]);
        $this->actingAs($user);

        $this->configurarParticipantesIncompletos();

        // Act
        $response = $this->get('/TorneoEjecucionLlave/accion=|GeneraLlave|parametros=id_torneo|999|idCategoria|1|LlaveNormal');

        // Assert
        $response->assertStatus(200);
        
        // Verificar mensaje de error
        $response->assertViewHas('Mensaje');
        $mensaje = $response->viewData('Mensaje');
        $this->assertStringContainsString('Faltan Grupos', $mensaje);
        
        // Verificar que NO se crearon partidos
        $partidosCreados = DB::table('torneo_fase_llave')
            ->where('idTorneo', 999)
            ->where('id_categoria', 1)
            ->count();
        
        $this->assertEquals(0, $partidosCreados);
    }

    /** @test */
    public function puede_generar_llave_especial()
    {
        // Arrange
        $user = User::factory()->create(['nivel' => 100]);
        $this->actingAs($user);
        $this->configurarParticipantesCompletos();

        // Act
        $response = $this->get('/TorneoEjecucionLlave/accion=|GeneraLlave|parametros=id_torneo|999|idCategoria|1|LlaveEspecial');

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('Mensaje');
        
        $partidosCreados = DB::table('torneo_fase_llave')->where('idTorneo', 999)->count();
        $this->assertGreaterThan(0, $partidosCreados);
    }

    /** @test */
    public function puede_generar_llave_con_tres_grupos_completos()
    {
        // Arrange
        $user = User::factory()->create(['nivel' => 100]);
        $this->actingAs($user);

        $this->configurarTresGruposCompletos();

        // Act
        $response = $this->get('/TorneoEjecucionLlave/accion=|GeneraLlave|parametros=id_torneo|999|idCategoria|1|LlaveNormal');

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('TorneoEjecucionLlave.index');
        
        // Verificar que se crearon partidos
        $partidosCreados = DB::table('torneo_fase_llave')
            ->where('idTorneo', 999)
            ->where('id_categoria', 1)
            ->count();
        
        $this->assertGreaterThan(0, $partidosCreados);
        
        // Verificar mensaje de éxito
        $response->assertViewHas('Mensaje');
        $mensaje = $response->viewData('Mensaje');
        $this->assertStringContainsString('Generada Correctamente', $mensaje);
    }

    /** @test */
    public function puede_generar_llave_con_grupos_mixtos()
    {
        // Arrange: Algunos grupos con 3 jugadores, otros con 2
        $user = User::factory()->create(['nivel' => 100]);
        $this->actingAs($user);

        $this->configurarGruposMixtos();

        // Act
        $response = $this->get('/TorneoEjecucionLlave/accion=|GeneraLlave|parametros=id_torneo|999|idCategoria|1|LlaveNormal');

        // Assert
        $response->assertStatus(200);
        $partidosCreados = DB::table('torneo_fase_llave')->where('idTorneo', 999)->count();
        $this->assertGreaterThan(0, $partidosCreados);
    }

    private function limpiarDatosPrueba()
    {
        // Limpiar solo los datos de prueba usando ID específico
        DB::table('torneo_fase_llave')->where('idTorneo', 999)->delete();
        DB::table('torneo_resultado_partido')->where('id_torneo', 999)->delete();
        DB::table('torneosparticipantes')->where('idTorneo', 999)->delete();
        DB::table('torneos')->where('id', 999)->delete();
        DB::table('users')->where('email', 'like', '%@example.%')->delete(); // Solo usuarios de prueba
    }

    private function configurarDatosBase()
    {
        // Usar ID 999 para evitar conflictos con datos reales
        DB::table('torneos')->updateOrInsert(
            ['id' => 999],
            [
                'nombreTorneo' => 'Torneo Test',
                'fechaTorneo' => now(),
                'activo' => 1
            ]
        );

        // Asegurar que existe la categoría
        DB::table('torneo_categoria')->updateOrInsert(
            ['idCategoria' => 1],
            ['categoria' => 'Principiante']
        );
    }

    private function configurarParticipantesCompletos()
    {
        // 6 participantes en 2 grupos completos (3 por grupo) usando ID 999
        $participantes = [
            // Grupo 1 - 3 participantes
            ['id' => 9001, 'nombreParticipante' => 'Test Jugador 1', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 1],
            ['id' => 9002, 'nombreParticipante' => 'Test Jugador 2', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 1],
            ['id' => 9003, 'nombreParticipante' => 'Test Jugador 3', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 3, 'grupo' => 1],
            
            // Grupo 2 - 3 participantes
            ['id' => 9004, 'nombreParticipante' => 'Test Jugador 4', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 2],
            ['id' => 9005, 'nombreParticipante' => 'Test Jugador 5', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 2],
            ['id' => 9006, 'nombreParticipante' => 'Test Jugador 6', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 3, 'grupo' => 2],
        ];

        foreach ($participantes as $participante) {
            DB::table('torneosparticipantes')->updateOrInsert(
                ['id' => $participante['id']],
                $participante
            );
        }
    }

    private function configurarParticipantesIncompletos()
    {
        // Grupo incompleto: solo 2 participantes SIN lugar asignado
        $participantes = [
            ['id' => 9001, 'nombreParticipante' => 'Test Jugador 1', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => null, 'grupo' => 1],
            ['id' => 9002, 'nombreParticipante' => 'Test Jugador 2', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => null, 'grupo' => 1],
        ];

        foreach ($participantes as $participante) {
            DB::table('torneosparticipantes')->updateOrInsert(
                ['id' => $participante['id']],
                $participante
            );
        }
    }

    private function configurarTresGruposCompletos()
    {
        // 9 participantes en 3 grupos completos (3 por grupo)
        $participantes = [
            // Grupo 1
            ['id' => 9001, 'nombreParticipante' => 'Test Jugador 1', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 1],
            ['id' => 9002, 'nombreParticipante' => 'Test Jugador 2', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 1],
            ['id' => 9003, 'nombreParticipante' => 'Test Jugador 3', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 3, 'grupo' => 1],
            
            // Grupo 2
            ['id' => 9004, 'nombreParticipante' => 'Test Jugador 4', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 2],
            ['id' => 9005, 'nombreParticipante' => 'Test Jugador 5', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 2],
            ['id' => 9006, 'nombreParticipante' => 'Test Jugador 6', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 3, 'grupo' => 2],
            
            // Grupo 3
            ['id' => 9007, 'nombreParticipante' => 'Test Jugador 7', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 3],
            ['id' => 9008, 'nombreParticipante' => 'Test Jugador 8', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 3],
            ['id' => 9009, 'nombreParticipante' => 'Test Jugador 9', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 3, 'grupo' => 3],
        ];

        foreach ($participantes as $participante) {
            DB::table('torneosparticipantes')->updateOrInsert(
                ['id' => $participante['id']],
                $participante
            );
        }
    }

    private function configurarGruposMixtos()
    {
        // Grupo 1: 3 jugadores, Grupo 2: 2 jugadores, Grupo 3: 3 jugadores
        $participantes = [
            // Grupo 1 - 3 participantes
            ['id' => 9001, 'nombreParticipante' => 'Test Jugador 1', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 1],
            ['id' => 9002, 'nombreParticipante' => 'Test Jugador 2', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 1],
            ['id' => 9003, 'nombreParticipante' => 'Test Jugador 3', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 3, 'grupo' => 1],
            
            // Grupo 2 - 2 participantes
            ['id' => 9004, 'nombreParticipante' => 'Test Jugador 4', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 2],
            ['id' => 9005, 'nombreParticipante' => 'Test Jugador 5', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 2],
            
            // Grupo 3 - 3 participantes
            ['id' => 9006, 'nombreParticipante' => 'Test Jugador 6', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 1, 'grupo' => 3],
            ['id' => 9007, 'nombreParticipante' => 'Test Jugador 7', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 2, 'grupo' => 3],
            ['id' => 9008, 'nombreParticipante' => 'Test Jugador 8', 'idTorneo' => 999, 'idCategoria' => 1, 'lugar' => 3, 'grupo' => 3],
        ];

        foreach ($participantes as $participante) {
            DB::table('torneosparticipantes')->updateOrInsert(
                ['id' => $participante['id']],
                $participante
            );
        }
    }
}