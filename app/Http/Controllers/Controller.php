<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "To-Do List API — SMK PESAT RPL",
    description: "Dokumentasi resmi seluruh endpoint API To-Do List (Laravel + Sanctum). Digunakan untuk integrasi frontend & pengujian API."
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Local Development Server"
)]
#[OA\Server(
    url: "/api",
    description: "Relative API Path"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Sanctum Token",
    description: "Masukkan Bearer token hasil dari endpoint login atau register. Format: 'Bearer {token}'"
)]
abstract class Controller
{
    //
}
