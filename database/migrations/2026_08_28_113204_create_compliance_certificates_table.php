<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('certificate_type', [
                'health_inspection',
                'catch_origin',
                'export_license',
                'safety_survey',
            ]);
            $table->string('certificate_number')->unique();
            $table->string('issuing_authority');
            $table->date('issued_at');
            $table->date('expires_at');
            $table->string('document_url')->nullable();
            $table->timestamps();

            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_certificates');
    }
};
