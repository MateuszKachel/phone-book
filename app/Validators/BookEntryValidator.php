<?php

declare(strict_types=1);

namespace App\Validators;

use App\DB;
use App\Repositories\BookEntryRepository;

class BookEntryValidator
{
    protected ?BookEntryRepository $bookEntryRepository = null;

    public function __construct(protected DB $db, protected ?int $id = null)
    {
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Imię jest wymagane';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Nazwisko jest wymagane';
        }

        if (empty($data['phone'])) {
            $errors['phone'] = 'Numer telefonu jest wymagany';
        } else {
            if (!preg_match('/^[0-9]{9}$/Du', $data['phone'])
                && !preg_match('/^[+]*[0-9]{11}$/Du', $data['phone'])
                && !preg_match('/^[+]*[0-9]{12}$/Du', $data['phone'])) {
                $errors['phone'] = 'Numer telefonu jest nieprawidłowy. Proszę podać numer telefonu w formacie 123456789 lub +48123456789.';
            } elseif (!$this->phoneIsUnique()) {
                $errors['phone'] = 'Ten numer telefonu został już wykorzystany. Proszę podać inny numer telefonu.';
            }
        }

        if (empty($data['email'])) {
            $errors['email'] = 'E-mail jest wymagany';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'E-mail jest nieprawidłowy';
        } elseif (!$this->emailIsUnique()) {
            $errors['email'] = 'Ten adres E-mail został już wykorzystany. Proszę podać inny adres E-mail.';
        }

        if (empty($data['address'])) {
            $errors['address'] = 'Adres jest wymagany';
        }

        return $errors;
    }

    public function validateCsrfToken(string $formId, string $token): bool
    {
        if (hash_equals(hash_hmac('sha256', $formId, $_SESSION['csrf_token']), $token)) {
            return true;
        }

        return false;
    }

    protected function emailIsUnique(): bool
    {
        $this->setupRepository();

        if ($this->bookEntryRepository->findByEmail($_POST['email'], $this->id)) {
            return false;
        }

        return true;
    }

    protected function phoneIsUnique(): bool
    {
        $this->setupRepository();

        if ($this->bookEntryRepository->findByPhone($_POST['phone'], $this->id)) {
            return false;
        }

        return true;
    }

    /**
     * @return void
     */
    protected function setupRepository(): void
    {
        if ($this->bookEntryRepository === null) {
            $this->bookEntryRepository = new BookEntryRepository($this->db);
        }
    }
}
