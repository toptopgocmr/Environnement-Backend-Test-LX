<?php
namespace App\Policies;

use App\Models\{User, Book};

class BookPolicy
{
    public function view(User $user, Book $book): bool
    {
        return $book->author_id === $user->id || $user->isAdmin();
    }

    public function update(User $user, Book $book): bool
    {
        return $book->author_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Book $book): bool
    {
        return $book->author_id === $user->id || $user->isAdmin();
    }
}
