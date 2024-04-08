<?php

declare(strict_types=1);

namespace App;

class App
{
    protected string $uri = '';

    public function start(): void
    {
        $this->prepareUri();

        // very basic routing
        match ($this->uri) {
            '' => $this->phoneBooksList(),
            '/add' => $this->addPhoneBook(),
            '/edit' => $this->editPhoneBook(),
            '/delete' => $this->deletePhoneBook(),
            default => $this->notFound(),
        };
    }

    protected function phoneBooksList(): void
    {
        $this->renderView('phoneBookEntries/index');
    }

    protected function addPhoneBook(): void
    {
        $this->renderView('phoneBookEntries/add');
    }

    protected function editPhoneBook(): void
    {
        $this->renderView('phoneBookEntries/edit');
    }

    protected function deletePhoneBook(): void
    {
    }

    protected function notFound(): void
    {
        $this->renderView('notFound');
    }

    protected function renderView(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include_once PROJECT_ROOT . '/views/header.phtml';
        include_once PROJECT_ROOT . '/views/' . $path . '.phtml';
        include_once PROJECT_ROOT . '/views/footer.phtml';
    }

    protected function prepareUri($uri = null): void
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);

        $this->uri = rtrim($uri, '/');
    }
}
