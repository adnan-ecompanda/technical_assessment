<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bulk_email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_email_campaign_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('email');
            $table->enum('status', ['pending', 'sent', 'invalid'])->default('pending');

            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['bulk_email_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_email_recipients');
    }
};
