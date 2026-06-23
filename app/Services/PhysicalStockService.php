<?php

namespace App\Services;

use App\Models\{Book, PhysicalStockMovement};
use Illuminate\Support\Facades\DB;

class PhysicalStockService
{
    /**
     * Ajoute du stock pour un livre et enregistre le mouvement.
     */
    public function addStock(int $bookId, int $quantity, string $reason = 'Ajout manuel', ?int $userId = null): PhysicalStockMovement
    {
        return DB::transaction(function () use ($bookId, $quantity, $reason, $userId) {
            $book = Book::lockForUpdate()->findOrFail($bookId);

            $stockAfter = $book->physical_stock + $quantity;
            $book->update(['physical_stock' => $stockAfter]);

            return PhysicalStockMovement::create([
                'book_id'     => $bookId,
                'type'        => 'in',
                'quantity'    => $quantity,
                'stock_after' => $stockAfter,
                'reason'      => $reason,
                'created_by'  => $userId,
            ]);
        });
    }

    /**
     * Retire du stock (vente ou ajustement).
     */
    public function removeStock(int $bookId, int $quantity, string $reason = 'Vente', ?int $userId = null): PhysicalStockMovement
    {
        return DB::transaction(function () use ($bookId, $quantity, $reason, $userId) {
            $book = Book::lockForUpdate()->findOrFail($bookId);

            $stockAfter = max(0, $book->physical_stock - $quantity);
            $book->update(['physical_stock' => $stockAfter]);

            return PhysicalStockMovement::create([
                'book_id'     => $bookId,
                'type'        => 'out',
                'quantity'    => $quantity,
                'stock_after' => $stockAfter,
                'reason'      => $reason,
                'created_by'  => $userId,
            ]);
        });
    }

    /**
     * Vérifie si un livre a du stock disponible.
     */
    public function isAvailable(int $bookId): bool
    {
        $book = Book::find($bookId);
        return $book && $book->physical_stock > 0;
    }

    /**
     * Réserve 1 exemplaire (utilisé lors de la commande physique).
     * Retourne false si plus de stock.
     */
    public function reserveStock(int $bookId, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($bookId, $userId) {
            $book = Book::lockForUpdate()->findOrFail($bookId);

            if ($book->physical_stock <= 0) {
                return false;
            }

            $stockAfter = $book->physical_stock - 1;
            $book->update(['physical_stock' => $stockAfter]);

            PhysicalStockMovement::create([
                'book_id'     => $bookId,
                'type'        => 'out',
                'quantity'    => 1,
                'stock_after' => $stockAfter,
                'reason'      => 'Réservation commande',
                'created_by'  => $userId,
            ]);

            return true;
        });
    }

    /**
     * Ajustement manuel du stock (inventaire).
     */
    public function adjustStock(int $bookId, int $newQuantity, string $reason = 'Ajustement inventaire', ?int $userId = null): PhysicalStockMovement
    {
        return DB::transaction(function () use ($bookId, $newQuantity, $reason, $userId) {
            $book = Book::lockForUpdate()->findOrFail($bookId);
            $book->update(['physical_stock' => $newQuantity]);

            return PhysicalStockMovement::create([
                'book_id'     => $bookId,
                'type'        => 'adjustment',
                'quantity'    => $newQuantity,
                'stock_after' => $newQuantity,
                'reason'      => $reason,
                'created_by'  => $userId,
            ]);
        });
    }
}
