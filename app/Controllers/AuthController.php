<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function index()
    {
        return view('login');
    }


    public function login()
    {
        $model = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $model->where('username', $username)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid username or password.');
        }

        session()->regenerate();

        session()->set([
            'isLoggedIn' => true,
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'] ?? null,
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to("/");
    }

    public function signUp()
    {
        try {
            $model = new UserModel();

            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');
            $confirm_password = $this->request->getPost('confirm_password');

            if ($password !== $confirm_password) {
                return redirect()->back()->withInput()->with('error', 'Password not match.');

            }

            $check = $model->where('username', $username)->first();

            if ($check) {
                return redirect()->back()->withInput()->with('error', 'Username already Taken.');
            }

            $model->insert([
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            return redirect()->to('/')->with('signup-success', 'Sign Up Successfully');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with("error", $e->getMessage());
        }
    }
}
