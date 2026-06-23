<?php
// database/migrations/2024_01_01_000003_create_lirex_v2_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ─── Formules de publication ──────────────────────────────────────────
        Schema::create('publication_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // Basic, Premium, Académique, Institutionnel
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_annual', 10, 2)->default(0);
            $table->string('currency', 3)->default('XAF');
            $table->integer('max_books')->default(1);         // nb livres autorisés
            $table->integer('max_file_size_mb')->default(50);
            $table->boolean('allow_physical')->default(false);// vente physique autorisée
            $table->boolean('allow_audio')->default(false);   // livre audio
            $table->boolean('allow_academic')->default(false);// thèses/mémoires
            $table->decimal('royalty_percent', 5, 2)->default(70.00); // % reversé
            $table->boolean('ai_review')->default(true);      // analyse IA obligatoire
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('features')->nullable();             // liste de features affichées
            $table->timestamps();
        });

        // ─── Abonnement auteur à une formule ─────────────────────────────────
        Schema::create('author_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('publication_plans')->cascadeOnDelete();
            $table->enum('billing', ['monthly', 'annual'])->default('monthly');
            $table->enum('status', ['pending_payment', 'active', 'expired', 'cancelled'])->default('pending_payment');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('currency', 3)->default('XAF');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });

        // ─── Demandes d'activation de compte ────────────────────────────────
        Schema::create('account_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['author', 'auditor', 'institution'])->default('author');
            $table->text('motivation')->nullable();           // pourquoi s'inscrire
            $table->string('document_path')->nullable();      // pièce d'identité / justificatif
            $table->string('institution_name')->nullable();
            $table->string('institution_country')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });

        // ─── Rapports d'analyse IA ────────────────────────────────────────────
        Schema::create('ai_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');

            // Scores (0-100)
            $table->unsignedTinyInteger('score_overall')->nullable();
            $table->unsignedTinyInteger('score_originality')->nullable();
            $table->unsignedTinyInteger('score_structure')->nullable();
            $table->unsignedTinyInteger('score_language')->nullable();
            $table->unsignedTinyInteger('score_norms')->nullable();  // normes ISO/académiques

            // Résultats
            $table->text('summary')->nullable();             // résumé IA
            $table->json('issues')->nullable();              // problèmes détectés
            $table->json('suggestions')->nullable();         // suggestions d'amélioration
            $table->boolean('isbn_valid')->nullable();
            $table->string('detected_language', 10)->nullable();
            $table->string('detected_document_type')->nullable();
            $table->boolean('plagiarism_flag')->default(false);
            $table->decimal('plagiarism_score', 5, 2)->nullable(); // % similarité
            $table->enum('recommendation', ['approve', 'review', 'reject'])->nullable();
            $table->text('admin_decision_note')->nullable();

            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index('book_id');
            $table->index('status');
            $table->index('recommendation');
        });

        // ─── Type de document (thèse, roman, mémoire…) ───────────────────────
        // Ajout de colonnes sur books via migration séparée (voir alter)
        Schema::table('books', function (Blueprint $table) {
            $table->enum('document_type', [
                'roman', 'nouvelle', 'poesie', 'essai', 'biographie',
                'these', 'memoire', 'article', 'manuel', 'rapport',
                'guide', 'revue', 'conference', 'autre'
            ])->default('roman')->after('format');
            $table->string('university')->nullable()->after('publisher');    // université (thèse)
            $table->string('supervisor')->nullable()->after('university');   // directeur de thèse
            $table->string('field_of_study')->nullable()->after('supervisor'); // domaine
            $table->string('keywords')->nullable()->after('field_of_study');  // mots-clés CSV
            $table->integer('physical_stock')->default(0)->after('print_price'); // stock physique
            $table->decimal('physical_price', 10, 2)->nullable()->after('physical_stock');
            $table->boolean('allow_rental')->default(false)->after('physical_price'); // lecture à la session
            $table->decimal('rental_price_hour', 10, 2)->nullable()->after('allow_rental');
        });

        // ─── Sessions de lecture louées ───────────────────────────────────────
        Schema::create('reading_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('currency', 3)->default('XAF');
            $table->integer('duration_hours')->default(24);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->integer('pages_read')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('token');
            $table->index('expires_at');
        });

        // ─── Stock physique (mouvements) ──────────────────────────────────────
        Schema::create('physical_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment'])->default('in');
            $table->integer('quantity');
            $table->integer('stock_after');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('book_id');
        });

        // ─── Adresses de livraison ────────────────────────────────────────────
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('phone', 20);
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 3)->default('CG');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('user_id');
        });

        // ─── Commandes physiques enrichies ────────────────────────────────────
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_address_id')->nullable()->after('shipping_status')
                  ->constrained('shipping_addresses')->nullOnDelete();
            $table->string('tracking_number')->nullable()->after('shipping_address_id');
            $table->string('carrier')->nullable()->after('tracking_number');
            $table->timestamp('shipped_at')->nullable()->after('carrier');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->text('shipping_note')->nullable()->after('delivered_at');
        });

        // ─── Conversations chat ───────────────────────────────────────────────
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'reader_author',   // lecteur ↔ auteur (depuis panier)
                'admin_author',    // admin ↔ auteur  (contenu/litiges)
                'admin_reader',    // admin ↔ lecteur (support)
                'support',         // support général
            ])->default('reader_author');
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete(); // contexte
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();// commande associée
            $table->string('subject')->nullable();
            $table->enum('status', ['open', 'closed', 'archived'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('last_message_at');
        });

        // ─── Participants d'une conversation ──────────────────────────────────
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
            $table->index('user_id');
        });

        // ─── Messages ─────────────────────────────────────────────────────────
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->enum('type', ['text', 'file', 'system'])->default('text');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('sender_id');
            $table->index('created_at');
        });

        // ─── Citations générées ───────────────────────────────────────────────
        Schema::create('citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('style', ['apa', 'mla', 'chicago', 'ieee', 'harvard'])->default('apa');
            $table->text('citation_text');
            $table->timestamps();

            $table->index('book_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citations');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chat_conversations');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_address_id','tracking_number','carrier','shipped_at','delivered_at','shipping_note']);
        });
        Schema::dropIfExists('shipping_addresses');
        Schema::dropIfExists('physical_stock_movements');
        Schema::dropIfExists('reading_sessions');
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['document_type','university','supervisor','field_of_study','keywords','physical_stock','physical_price','allow_rental','rental_price_hour']);
        });
        Schema::dropIfExists('ai_reviews');
        Schema::dropIfExists('account_requests');
        Schema::dropIfExists('author_plans');
        Schema::dropIfExists('publication_plans');
    }
};
