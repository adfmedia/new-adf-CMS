<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('heading')->nullable();
            $table->text('content')->nullable();

            $table->text('structure')->nullable();

            $table->string('submit_text')->nullable();
            $table->boolean('marketing_opt_in')->default(0);
            $table->string('marketing_opt_in_text')->nullable();
            $table->string('marketing_opt_out_text')->nullable();
            $table->boolean('use_recaptcha')->default(0);
            $table->string('redirect_link')->nullable();

            $table->text('internal_recipients')->nullable();
            $table->string('internal_subject')->nullable();

            $table->boolean('send_auto_response')->default(0);
            $table->string('auto_response_subject')->nullable();
            $table->text('auto_response_content')->nullable();
            $table->string('auto_response_attachment')->nullable();

            $table->string('ga4_event')->nullable();
            $table->boolean('status')->default(0);

            $table->string('code_reference')->nullable();
            $table->string('template')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('code_reference');
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('form_id');
            $table->string('label');
            $table->string('help_text')->nullable();
            $table->string('type');
            $table->text('options')->nullable();
            $table->text('settings')->nullable();
            $table->boolean('required')->default(0);
            $table->string('code_reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('form_id');
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->integer('form_id');
            $table->text('submission_url');
            $table->boolean('marketing_opt_in')->default(0);
            $table->text('marketing_opt_in_text')->nullable();
            $table->dateTime('marketing_opt_in_date')->nullable();
            $table->timestamps();
        });
        Schema::create('form_submission_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('form_submission_id');
            $table->integer('form_field_id');
            $table->string('label');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_submission_fields');
    }
};
