<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    public function up(): void

    {

        Schema::create('marcaciones', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('empleado_id');

            $table->enum('tipo_marcacion', ['ingreso', 'salida', 'almuerzo_inicio', 'almuerzo_fin']);

            $table->timestamp('timestamp');

            $table->timestamps();

            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');

        });

    }



    public function down(): void

    {

        Schema::dropIfExists('marcaciones');

    }

};

