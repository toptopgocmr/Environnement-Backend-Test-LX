<?php
namespace App\Http\Controllers\Api\V1;

/**
 * DEPRECATED — this file used to bundle legacy duplicate definitions of
 * AuthController, BookController, OrderController, CategoryController,
 * LibraryController and AuthorApiController — all already defined (and
 * actively used by routes/api.php) in their own dedicated files in this
 * same directory/namespace.
 *
 * Having two files declare the exact same fully-qualified class name is
 * unsafe: Composer's classmap autoloader (used whenever
 * `composer install --optimize-autoloader` / `artisan optimize` runs)
 * resolves such collisions by file-scan order, not by intent. It happened
 * to pick the dedicated files this time, but a future autoload rebuild
 * could just as easily pick the stale copies in this file instead —
 * silently reverting every fix made to the real controllers, with no
 * error or warning surfaced anywhere.
 *
 * The class bodies have been removed to eliminate that risk. All real
 * logic lives in AuthController.php, BookController.php, OrderController.php,
 * CategoryController.php, LibraryController.php and AuthorApiController.php
 * in this same directory. This file is kept only so any stray reference to
 * it fails loudly (file not found / class not found) instead of silently
 * loading stale logic. It is safe to delete entirely.
 */
