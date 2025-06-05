<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', function (Request $request) {
    if ($request->ajax() || $request->wantsJson()) {
        return User::select(['id', 'name', 'email'])->latest()->paginate(5);
    }
    $users = User::select(['id', 'name', 'email'])->latest()->paginate(5);
    return view('users', compact('users'));
});

Route::post('/users', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|min:3|max:255',
        'email' => 'required|email|max:255|unique:users',
    ]);

    // Jika validasi sukses
    $validated['password'] = bcrypt('password');

    $user = User::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'User berhasil ditambahkan',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]
    ]);
});

Route::put('/users/{id}', function (Request $request, $id) {
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan'
        ]);
    }

    if ($user->email === $request->email && $user->name === $request->name) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada perubahan data'
        ]);
    }

    $validated = $request->validate([
        'name' => 'required|string|min:3|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $id,
    ]);

    $user->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'User berhasil diperbarui',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]
    ]);
});

Route::delete('/users/{id}', function ($id) {
    $user = User::findOrFail($id);
    $user->delete();

    return response()->json([
        'success' => true,
        'message' => 'User berhasil dihapus.'
    ]);
});
