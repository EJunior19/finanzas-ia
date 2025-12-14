#!/bin/bash

echo "🚀 Creando estructura base Finanzas IA..."

# MODELOS + MIGRACIONES
php artisan make:model FinancialEvent -m
php artisan make:model AiExpectation -m
php artisan make:model AiMemoryItem -m
php artisan make:model AiQuestion -m

# CONTROLADORES
php artisan make:controller DashboardController
php artisan make:controller FinancialEventController
php artisan make:controller DebtController
php artisan make:controller AIController

# VISTAS
mkdir -p resources/views/layouts
mkdir -p resources/views/dashboard
mkdir -p resources/views/ai
mkdir -p resources/views/events

touch resources/views/layouts/app.blade.php
touch resources/views/dashboard/index.blade.php
touch resources/views/ai/inbox.blade.php
touch resources/views/events/create.blade.php
touch resources/views/events/list.blade.php

echo "✅ Estructura creada correctamente"
