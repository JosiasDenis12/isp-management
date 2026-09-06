<?php

class ConfiguracionController
{
    public function index(): void
    {
        $data = [
            'title' => 'Configuración - ' . APP_NAME,
        ];

        extract($data);
        require_once 'views/configuracion/index.php';
    }
}
