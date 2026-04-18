<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet_landing_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('section')->index();
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('type')->default('text');
            $table->timestamps();

            $table->unique(['outlet_id', 'key'], 'outlet_landing_contents_outlet_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_landing_contents');
    }
};
