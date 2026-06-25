<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResponseService
{
    public function success(Request $request, string $message, string $redirect): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirect,
            ]);
        }

        return redirect($redirect)->with('success', $message);
    }

    public function error(Request $request, string $message, int $status = 500): JsonResponse|RedirectResponse
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('error', $message);
    }
}
