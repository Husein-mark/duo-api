<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "To-Do List API",
    description: "Dokumentasi API To-Do List Duo Project SMK PESAT RPL (Laravel + Sanctum)"
)]
#[OA\Server(
    url: "/api",
    description: "Base API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "Sanctum Token",
    description: "Masukkan token yang diperoleh dari endpoint login atau register (contoh: 1|abcdef123...)"
)]
abstract class Controller
{
    //
}
