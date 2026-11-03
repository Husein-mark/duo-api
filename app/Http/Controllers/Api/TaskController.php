<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    #[OA\Get(
        path: "/tasks",
        summary: "Daftar Tugas Pengguna",
        tags: ["Tugas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "Filter status tugas (todo / done)",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["todo", "done"])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Daftar tugas berhasil diambil",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Daftar tugas berhasil diambil"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "title", type: "string", example: "Mengerjakan tugas PKL"),
                                    new OA\Property(property: "deadline", type: "string", format: "date", example: "2026-11-20"),
                                    new OA\Property(property: "status", type: "string", example: "todo"),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated."),
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->tasks();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $tasks = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tugas berhasil diambil',
            'data' => TaskResource::collection($tasks),
        ], 200);
    }

    #[OA\Post(
        path: "/tasks",
        summary: "Tambah Tugas Baru",
        tags: ["Tugas"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "deadline"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Mengerjakan tugas PKL"),
                    new OA\Property(property: "deadline", type: "string", format: "date", example: "2026-11-20"),
                    new OA\Property(property: "status", type: "string", enum: ["todo", "done"], example: "todo"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Tugas berhasil ditambahkan",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Tugas berhasil ditambahkan"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "title", type: "string", example: "Mengerjakan tugas PKL"),
                                new OA\Property(property: "deadline", type: "string", format: "date", example: "2026-11-20"),
                                new OA\Property(property: "status", type: "string", example: "todo"),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated."),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validasi gagal",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validasi gagal"),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            example: [
                                "title" => ["Judul wajib diisi."],
                                "deadline" => ["Format tanggal harus YYYY-MM-DD."],
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create([
            'title' => $request->title,
            'deadline' => $request->deadline,
            'status' => $request->input('status', 'todo'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil ditambahkan',
            'data' => new TaskResource($task),
        ], 201);
    }

    #[OA\Get(
        path: "/tasks/{id}",
        summary: "Detail Tugas",
        tags: ["Tugas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID tugas",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detail tugas berhasil diambil",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Detail tugas berhasil diambil"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "title", type: "string", example: "Mengerjakan tugas PKL"),
                                new OA\Property(property: "deadline", type: "string", format: "date", example: "2026-11-20"),
                                new OA\Property(property: "status", type: "string", example: "todo"),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated."),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Tugas milik pengguna lain",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Anda tidak berhak mengakses tugas ini"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tugas tidak ditemukan",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Tugas tidak ditemukan"),
                    ]
                )
            ),
        ]
    )]
    public function show(Task $task): JsonResponse
    {
        Gate::authorize('view', $task);

        return response()->json([
            'success' => true,
            'message' => 'Detail tugas berhasil diambil',
            'data' => new TaskResource($task),
        ], 200);
    }

    #[OA\Put(
        path: "/tasks/{id}",
        summary: "Ubah Tugas",
        tags: ["Tugas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID tugas",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "deadline"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Mengerjakan laporan PKL"),
                    new OA\Property(property: "deadline", type: "string", format: "date", example: "2026-11-25"),
                    new OA\Property(property: "status", type: "string", enum: ["todo", "done"], example: "todo"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Tugas berhasil diubah",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Tugas berhasil diubah"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "title", type: "string", example: "Mengerjakan laporan PKL"),
                                new OA\Property(property: "deadline", type: "string", format: "date", example: "2026-11-25"),
                                new OA\Property(property: "status", type: "string", example: "todo"),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated."),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Tugas milik pengguna lain",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Anda tidak berhak mengakses tugas ini"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tugas tidak ditemukan",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Tugas tidak ditemukan"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validasi gagal",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validasi gagal"),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            example: ["title" => ["Judul wajib diisi."]]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('update', $task);

        $data = [
            'title' => $request->title,
            'deadline' => $request->deadline,
        ];

        if ($request->has('status') && $request->status !== null) {
            $data['status'] = $request->status;
        }

        $task->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diubah',
            'data' => new TaskResource($task->fresh()),
        ], 200);
    }

    #[OA\Patch(
        path: "/tasks/{id}/status",
        summary: "Ubah Status Tugas (Selesai / Belum)",
        tags: ["Tugas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID tugas",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["todo", "done"], example: "done"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Status tugas berhasil diubah",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Status tugas berhasil diubah"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "title", type: "string", example: "Mengerjakan laporan PKL"),
                                new OA\Property(property: "deadline", type: "string", format: "date", example: "2026-11-25"),
                                new OA\Property(property: "status", type: "string", example: "done"),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated."),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Tugas milik pengguna lain",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Anda tidak berhak mengakses tugas ini"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tugas tidak ditemukan",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Tugas tidak ditemukan"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validasi gagal",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validasi gagal"),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            example: ["status" => ["Status harus berupa todo atau done."]]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('update', $task);

        $task->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diubah',
            'data' => new TaskResource($task->fresh()),
        ], 200);
    }

    #[OA\Delete(
        path: "/tasks/{id}",
        summary: "Hapus Tugas",
        tags: ["Tugas"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID tugas",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tugas berhasil dihapus",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Tugas berhasil dihapus"),
                        new OA\Property(property: "data", type: "null", example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Unauthenticated."),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Tugas milik pengguna lain",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Anda tidak berhak mengakses tugas ini"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tugas tidak ditemukan",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Tugas tidak ditemukan"),
                    ]
                )
            ),
        ]
    )]
    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus',
            'data' => null,
        ], 200);
    }
}
