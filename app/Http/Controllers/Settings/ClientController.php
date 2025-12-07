<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $query = User::query();
        if ($q) {
            $query->where(function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.clients.index', compact('users', 'q'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
        ]);

        // Normalize name: prefer explicit 'name', otherwise join firstname + lastname
        $name = trim(($data['name'] ?? '') ?: (($data['firstname'] ?? '').' '.($data['lastname'] ?? '')));
        if (empty($name)) {
            $name = 'Cliente '.substr(uniqid(), -6);
        }

        $payload = [
            'name' => $name,
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        } else {
            $payload['password'] = bcrypt(\Illuminate\Support\Str::random(10));
        }

        $user = User::create($payload);

        return redirect()->route('admin.clients.index')->with('success', 'Cliente creado.');
    }

    public function edit(User $client)
    {
        return view('admin.clients.edit', ['user' => $client]);
    }

    public function update(Request $request, User $client)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,'.$client->id,
            'password' => 'nullable|string|min:6',
        ]);

        $name = trim(($data['name'] ?? '') ?: (($data['firstname'] ?? '').' '.($data['lastname'] ?? '')));
        if (! empty($name)) {
            $payload['name'] = $name;
        }

        $payload['email'] = $data['email'];

        if (! empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }

        $client->update($payload ?? []);

        return redirect()->route('admin.clients.index')->with('success', 'Cliente actualizado.');
    }

    public function show(User $client)
    {
        return view('admin.clients.show', ['user' => $client]);
    }

    public function destroy(User $client)
    {
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Cliente eliminado.');
    }
}
