<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DB;
use App\Models\BookEntry;

class BookEntryRepository
{
    public function __construct(protected DB $db)
    {
    }

    public function findAll(): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM phone_book_entries');

        return array_map(fn($row) => $this->mapRowToBookEntry($row), $rows);
    }

    public function find(int $id): ?BookEntry
    {
        $row = $this->db->fetch('SELECT * FROM phone_book_entries WHERE id = ?', [$id]);

        return $row ? $this->mapRowToBookEntry($row) : null;
    }

    public function findByEmail(string $email, ?int $id): ?BookEntry
    {
        $query = $id
            ? 'SELECT * FROM phone_book_entries WHERE email = ? AND id != ?'
            : 'SELECT * FROM phone_book_entries WHERE email = ?';

        $row = $this->db->fetch($query, $id ? [$email, $id] : [$email]);

        return $row ? $this->mapRowToBookEntry($row) : null;
    }

    public function findByPhone(mixed $phone, ?int $id)
    {
        $query = $id
            ? 'SELECT * FROM phone_book_entries WHERE phone = ? AND id != ?'
            : 'SELECT * FROM phone_book_entries WHERE phone = ?';

        $row = $this->db->fetch($query, $id ? [$phone, $id] : [$phone]);

        return $row ? $this->mapRowToBookEntry($row) : null;
    }

    public function save(BookEntry|array $bookEntry): int
    {
        $isObj = $bookEntry instanceof BookEntry;

        $data = [
            'first_name' => $isObj ? $bookEntry->getFirstName() : $bookEntry['first_name'],
            'last_name' => $isObj ? $bookEntry->getLastName() : $bookEntry['last_name'],
            'phone' => $isObj ? $bookEntry->getPhone() : $bookEntry['phone'],
            'email' => $isObj ? $bookEntry->getEmail() : $bookEntry['email'],
            'address' => $isObj ? $bookEntry->getAddress() : $bookEntry['address'],
        ];

        $data['email'] = mb_strtolower($data['email'], 'UTF-8');

        if (($isObj && $bookEntry->getId() === null)
            || (!$isObj && !isset($bookEntry['id']))) {
            return $this->db->insert('phone_book_entries', $data);
        } else {
            $data['id'] = $isObj ? $bookEntry->getId() : $bookEntry['id'];
            $this->db->query(
                'UPDATE phone_book_entries 
                    SET first_name = ?, last_name = ?, phone = ?, email = ?, address = ?
                    WHERE id = ?',
                array_values($data)
            );

            return (int)$data['id'];
        }
    }

    public function delete(int $id): void
    {
        $this->db->query('DELETE FROM phone_book_entries WHERE id = ?', [$id]);
    }

    private function mapRowToBookEntry(array $row): BookEntry
    {
        return new BookEntry(
            $row['first_name'],
            $row['last_name'],
            $row['phone'],
            $row['email'],
            $row['address'],
            (int)$row['id']
        );
    }
}
