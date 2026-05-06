<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alerts - actionable notifications for decision-makers.
     * 
     * The entire system exists to produce these.
     * 
     * Lifecycle:
     * 1. Risk engine calculates HIGH score
     * 2. Alert created (status: pending)
     * 3. Alert sent via SMS/WhatsApp (status: sent)
     * 4. Decision-maker takes action
     * 5. Alert resolved (status: resolved)
     * 
     * Example:
     * "HIGH cholera risk detected in Mora district.
     *  7,600 children at risk.
     *  Pre-position ORS at all health posts immediately."
     */
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // What and where
            $table->uuid('district_id');
            $table->uuid('hazard_type_id');
            
            // Link to risk score that triggered this
            $table->uuid('risk_score_id');
            
            // Alert content
            $table->string('title', 200);
            $table->text('message');
            $table->text('recommended_action')->nullable();
            
            // Severity
            $table->enum('severity', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']);
            $table->integer('priority')->default(5); // 1-10
            
            // Who should receive this?
            $table->uuid('organization_id')->nullable();
            
            // Recipients (JSON array of phone numbers, emails, user IDs)
            $table->jsonb('recipients')->nullable();
            
            // Alert status
            $table->enum('status', [
                'pending',      // Created, not yet sent
                'sent',         // Delivered to recipients
                'acknowledged', // Recipient confirmed receipt
                'resolved',     // Action taken, risk mitigated
                'expired',      // Time window passed
                'cancelled'     // Alert cancelled (false alarm)
            ])->default('pending');
            
            // Timestamps
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Who acted on this?
            $table->uuid('acknowledged_by_user_id')->nullable();
            $table->uuid('resolved_by_user_id')->nullable();
            
            // Resolution details
            $table->text('resolution_notes')->nullable();
            
            // Delivery tracking
            $table->jsonb('delivery_status')->nullable(); // per-recipient status
            
            // Metadata for additional context
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('district_id')
                  ->references('id')
                  ->on('districts')
                  ->onDelete('cascade');
            
            $table->foreign('hazard_type_id')
                  ->references('id')
                  ->on('hazard_types')
                  ->onDelete('cascade');
            
            $table->foreign('risk_score_id')
                  ->references('id')
                  ->on('risk_scores')
                  ->onDelete('cascade');
            
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('set null');
            
            $table->foreign('acknowledged_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            $table->foreign('resolved_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('district_id');
            $table->index('hazard_type_id');
            $table->index('risk_score_id');
            $table->index('organization_id');
            $table->index('severity');
            $table->index('status');
            $table->index('priority');
            $table->index('sent_at');
            $table->index('expires_at');
            $table->index(['status', 'severity']); // Common query pattern
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};