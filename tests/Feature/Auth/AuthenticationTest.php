<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'password' => 'Kata sandi yang Anda masukkan tidak sesuai. Silakan periksa kembali.',
    ]);
});

test('users can not authenticate with unregistered email', function () {
    $response = $this->post('/login', [
        'email' => 'unregistered@example.com',
        'password' => 'secret123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => 'Alamat email tidak terdaftar dalam sistem.',
    ]);
});

test('users cannot authenticate if employee status is inactive', function (string $status) {
    $user = User::factory()->create();
    \App\Models\Pegawai::create([
        'user_id' => $user->id,
        'nip' => '198001012010011999',
        'jabatan' => 'Staff Administrasi',
        'status_pegawai' => $status,
        'tanggal_masuk' => '2010-01-01',
        'sisa_cuti_n' => 12,
        'sisa_cuti_n1' => 0,
        'sisa_cuti_n2' => 0,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors([
        'email' => 'Akun tidak aktif karena status kepegawaian saat ini: ' . $status . '. Silakan hubungi admin kepegawaian.',
    ]);
})->with(['Pensiun', 'Mutasi', 'Berhenti']);

test('users are logged out when their employee status becomes inactive', function () {
    $user = User::factory()->create();
    $pegawai = \App\Models\Pegawai::create([
        'user_id' => $user->id,
        'nip' => '198001012010011998',
        'jabatan' => 'Staff Administrasi',
        'status_pegawai' => 'Aktif',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $pegawai->update(['status_pegawai' => 'Mutasi']);

    $response = $this->get(route('profile.edit'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => 'Akun tidak aktif karena status kepegawaian saat ini: Mutasi. Silakan hubungi admin kepegawaian.',
    ]);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('users can authenticate with remember me', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    
    // Cookie 'remember_web_...' harus ada di response
    $recallerName = Auth::guard('web')->getRecallerName();
    $response->assertCookie($recallerName);

    // remember_token harus tersimpan di database pada model user
    $this->assertNotNull($user->fresh()->remember_token);
});

test('users authenticate without remember me does not set remember cookie', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $recallerName = Auth::guard('web')->getRecallerName();
    $response->assertCookieMissing($recallerName);
});

test('users can automatically reauthenticate via remember me cookie when session expires', function () {
    $user = User::factory()->create();
    \App\Models\Pegawai::create([
        'user_id' => $user->id,
        'nip' => '199001012020011999',
        'jabatan' => 'Staff IT',
        'tanggal_masuk' => '2020-01-01',
        'sisa_cuti_n' => 12,
        'sisa_cuti_n1' => 0,
        'sisa_cuti_n2' => 0,
    ]);

    // 1. Login dengan remember me
    $loginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ]);

    $recallerName = Auth::guard('web')->getRecallerName();
    $cookie = $loginResponse->getCookie($recallerName);
    $this->assertNotNull($cookie);

    // 2. Simulasikan browser ditutup / session hilang (reset state auth & session)
    $this->flushSession();
    Auth::forgetGuards();

    // 3. Akses dashboard hanya dengan membawa cookie remember me
    $recallerValue = $user->id . '|' . $user->fresh()->remember_token . '|' . $user->password;
    $response = $this->withCookie($recallerName, $recallerValue)
                     ->get('/dashboard');

    // User otomatis terotentikasi dan berhasil mengakses dashboard
    $this->assertAuthenticatedAs($user);
    $response->assertStatus(200);
});
