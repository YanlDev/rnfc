<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matriz por defecto: rol => permisos concedidos.
     * El Admin la edita después desde la UI.
     */
    private array $defaults = [
        'administrador' => [
            'documento.ver', 'documento.subir', 'documento.eliminar', 'carpeta.gestionar',
            'cuaderno.ver', 'cuaderno.escribir', 'cuaderno.eliminar',
            'calendario.ver', 'calendario.gestionar',
            'equipo.gestionar', 'obra.editar',
        ],
        'residente' => [
            'documento.ver', 'documento.subir', 'documento.eliminar', 'carpeta.gestionar',
            'cuaderno.ver', 'cuaderno.escribir',
            'calendario.ver', 'calendario.gestionar',
        ],
        'especialista' => [
            'documento.ver', 'documento.subir',
            'cuaderno.ver', 'calendario.ver',
        ],
        'asistente' => [
            'documento.ver', 'documento.subir',
            'cuaderno.ver', 'calendario.ver',
        ],
    ];

    public function up(): void
    {
        Schema::create('permisos_obra', function (Blueprint $table) {
            $table->id();
            $table->string('rol_obra');
            $table->string('permiso');
            $table->unique(['rol_obra', 'permiso']);
        });

        $filas = [];

        foreach ($this->defaults as $rol => $permisos) {
            foreach ($permisos as $permiso) {
                $filas[] = ['rol_obra' => $rol, 'permiso' => $permiso];
            }
        }

        DB::table('permisos_obra')->insertOrIgnore($filas);
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_obra');
    }
};
