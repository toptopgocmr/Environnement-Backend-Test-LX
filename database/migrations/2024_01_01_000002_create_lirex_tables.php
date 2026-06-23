<?php
// database/migrations/2024_01_01_000002_create_lirex_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ─── Categories ──────────────────────────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon', 20)->nullable();           // emoji
            $table->string('color', 10)->default('#2563EB');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });

        // ─── Books ───────────────────────────────────────────────────────────
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('cover_image')->nullable();
            $table->string('file_path')->nullable();
            $table->string('preview_path')->nullable();       // extrait gratuit (10%)
            $table->enum('format', ['pdf', 'epub', 'both'])->default('pdf');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('XAF');
            $table->boolean('is_free')->default(false);
            $table->string('language', 5)->default('fr');
            $table->integer('pages')->nullable()->unsigned();
            $table->string('isbn', 20)->nullable();
            $table->year('publication_year')->nullable();
            $table->string('publisher')->nullable();
            $table->enum('status', ['draft', 'pending', 'published', 'rejected', 'suspended'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('downloads')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('print_on_demand')->default(false);
            $table->decimal('print_price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('author_id');
            $table->index('category_id');
            $table->index('is_featured');
            $table->index('is_free');
            $table->fullText(['title', 'description']); // MySQL FULLTEXT
        });

        // ─── Book tags ────────────────────────────────────────────────────────
        Schema::create('book_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('tag', 100);

            $table->index('tag');
        });

        // ─── Orders ───────────────────────────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('type', ['digital', 'print'])->default('digital');
            $table->enum('payment_method', ['mtn_momo', 'airtel_money', 'stripe', 'free'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->json('payment_data')->nullable();
            $table->string('download_token', 64)->nullable()->unique();
            $table->unsignedTinyInteger('download_count')->default(0);
            $table->unsignedTinyInteger('max_downloads')->default(3);
            $table->timestamp('expires_at')->nullable();
            // champs impression
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_phone', 20)->nullable();
            $table->enum('shipping_status', ['none', 'processing', 'shipped', 'delivered'])->default('none');
            $table->timestamps();

            $table->index('user_id');
            $table->index('book_id');
            $table->index('payment_status');
            $table->index('transaction_id');
        });

        // ─── Royalties ────────────────────────────────────────────────────────
        Schema::create('royalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('platform_fee', 10, 2);   // 20% plateforme
            $table->decimal('net_amount', 10, 2);      // 80% auteur
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('author_id');
            $table->index('status');
        });

        // ─── Reviews ─────────────────────────────────────────────────────────
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['book_id', 'user_id']);
            $table->index('is_approved');
        });

        // ─── Wishlists ────────────────────────────────────────────────────────
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'book_id']);
        });

        // ─── Reading progress ─────────────────────────────────────────────────
        Schema::create('reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_page')->default(1);
            $table->unsignedInteger('total_pages')->default(1);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'book_id']);
        });

        // ─── Notifications ────────────────────────────────────────────────────
        // MySQL: uuid stocké en CHAR(36)
        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();            // UUID en CHAR(36) pour MySQL
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
        });

        // ─── Withdrawal requests ──────────────────────────────────────────────
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('method', ['mtn_momo', 'airtel_money', 'bank'])->default('mtn_momo');
            $table->string('account_number', 20);
            $table->string('account_name');
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->decimal('balance_before', 10, 2)->default(0.00);
            $table->decimal('balance_after', 10, 2)->default(0.00);
            $table->timestamps();

            $table->index('author_id');
            $table->index('status');
        });

        // ─── Author follows ───────────────────────────────────────────────────
        Schema::create('author_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['follower_id', 'author_id']);
        });

        // ─── Subscriptions ────────────────────────────────────────────────────
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('plan', ['monthly', 'annual', 'institutional'])->default('monthly');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('XAF');
            $table->enum('status', ['active', 'cancelled', 'expired'])->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('ends_at');
        });

        // ─── Platform settings ────────────────────────────────────────────────
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('author_follows');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reading_progress');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('royalties');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('book_tags');
        Schema::dropIfExists('books');
        Schema::dropIfExists('categories');
    }
};
