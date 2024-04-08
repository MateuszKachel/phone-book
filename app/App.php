<?php

declare(strict_types=1);

namespace App;

use App\Repositories\BookEntryRepository;
use App\Validators\BookEntryValidator;
use Exception;
use PDOException;

class App
{
    protected string $uri = '';
    protected ?DB $db;

    public function start(): void
    {
        if (!session_id()) {
            session_start();
        }

        $this->prepareUri();
        $this->db = new DB('sqlite:' . PROJECT_ROOT . '/database/database.sqlite');

        // very basic routing
        match ($this->uri) {
            '' => $this->phoneBooksList(),
            '/add' => $this->addPhoneBook(),
            '/edit' => $this->editPhoneBook($_SERVER['REQUEST_URI']),
            default => $this->notFound(),
        };
    }

    protected function phoneBooksList(): void
    {
        $bookRepository = new BookEntryRepository($this->db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->deletePhoneBook();
        }

        try {
            $phoneBooksEntries = $bookRepository->findAll();
        } catch (PDOException) {
            // temp solution, should be handled better
            $this->createTable();
            $phoneBooksEntries = [];
        }

        $this->renderView(
            'phoneBookEntries/index',
            ['phoneBooksEntries' => $phoneBooksEntries]
        );
    }

    protected function addPhoneBook(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['_token']) || !(new BookEntryValidator($this->db))->validateCsrfToken(
                    'add_phone_book_entry',
                    $_POST['_token']
                )) {
                header('Location: /');
                die();
            }

            $cleanData = $this->cleanPhoneBookData($_POST);
            $errors = (new BookEntryValidator($this->db))->validate($cleanData);
            if ($errors) {
                $this->renderView(
                    'phoneBookEntries/add',
                    [
                        'errors' => $errors,
                        'entry' => $cleanData
                    ]
                );
                return;
            }

            $bookRepository = new BookEntryRepository($this->db);
            $bookRepository->save($cleanData);
            header('Location: /');
            die();
        }

        $this->renderView('phoneBookEntries/add');
    }

    protected function editPhoneBook(string $uri): void
    {
        if (!preg_match('|/edit\?id=(\d+)|', $uri, $matches)) {
            header('Location: /');
            die();
        }

        $id = (int)($matches[1]);
        $bookRepository = new BookEntryRepository($this->db);
        $phoneBook = $bookRepository->find($id);
        if (!$phoneBook) {
            header('Location: /');
            die();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['_token']) || !(new BookEntryValidator($this->db, $id))->validateCsrfToken(
                    'edit_phone_book_entry_' . $id,
                    $_POST['_token']
                )) {
                header('Location: /');
                die();
            }

            $cleanData = $this->cleanPhoneBookData($_POST);
            $errors = (new BookEntryValidator($this->db, $id))->validate($cleanData);
            if ($errors) {
                $this->renderView(
                    'phoneBookEntries/edit',
                    [
                        'id' => $id,
                        'errors' => $errors,
                        'entry' => $cleanData
                    ]
                );
                return;
            }

            $cleanData['id'] = $id;
            $bookRepository->save($cleanData);
            header('Location: /');
            die();
        }

        $this->renderView('phoneBookEntries/edit', [
            'id' => $id,
            'firstName' => $phoneBook->getFirstName(),
            'lastName' => $phoneBook->getLastName(),
            'phone' => $phoneBook->getPhone(),
            'email' => $phoneBook->getEmail(),
            'address' => $phoneBook->getAddress(),
        ]);
    }

    protected function deletePhoneBook(): void
    {
        if (!isset($_POST['id'])
            || !ctype_digit($_POST['id'])
            || !isset($_POST['method'])
            || empty($_POST['_token'])
            || $_POST['method'] !== 'DELETE'
            || !(new BookEntryValidator($this->db))->validateCsrfToken(
                'edit_phone_book_entry_' . $_POST['id'],
                $_POST['_token']
            )) {
            return;
        };

        $id = (int)($_POST['id']);
        if ($id) {
            $bookRepository = new BookEntryRepository($this->db);
            $bookRepository->delete($id);
            header('Location: /');
            die();
        }
    }

    protected function notFound(): void
    {
        $this->renderView('notFound');
    }

    protected function cleanPhoneBookData(array $postData): array
    {
        // take only known fields
        $postData = array_filter(
            $postData,
            fn($key) => in_array($key, ['first_name', 'last_name', 'phone', 'email', 'address']),
            ARRAY_FILTER_USE_KEY
        );

        $postData = array_map('strip_tags', $postData);
        return array_map('trim', $postData);
    }

    protected function renderView(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include_once PROJECT_ROOT . '/views/layout/header.phtml';
        include_once PROJECT_ROOT . '/views/' . $path . '.phtml';
        include_once PROJECT_ROOT . '/views/layout/footer.phtml';
    }

    protected function prepareUri($uri = null): void
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);

        $this->uri = rtrim($uri, '/');
    }

    protected function createTable(): void
    {
        try {
            $this->db->beginTransaction();

            $this->db->query(
                'CREATE TABLE IF NOT EXISTS phone_book_entries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                phone TEXT NOT NULL,
                email TEXT NOT NULL,
                address TEXT NOT NULL
            )'
            );

            $this->db->query('CREATE INDEX idx_phone_book_entries_first_name ON phone_book_entries (first_name)');
            $this->db->query('CREATE INDEX idx_phone_book_entries_last_name ON phone_book_entries (last_name)');
            $this->db->commit();
        } catch (Exception) {
            $this->db->rollBack();
        }

        header('Location: /');
        die();
    }
}
