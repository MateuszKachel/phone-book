<?php

declare(strict_types=1);

namespace App\Models;

class BookEntry
{
    public function __construct(
        protected string $firstName,
        protected string $lastName,
        protected string $phone,
        protected string $email,
        protected string $address,
        protected ?int $id = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getAddress(): string
    {
        return $this->address;
    }
}
