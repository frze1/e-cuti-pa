<?php

use App\Models\User;

test('manifest.json exists and satisfies PWA requirements', function () {
    $manifestPath = public_path('manifest.json');
    expect(file_exists($manifestPath))->toBeTrue();

    $content = json_decode(file_get_contents($manifestPath), true);
    expect($content)->toBeArray();
    expect($content)->toHaveKeys(['name', 'short_name', 'start_url', 'display', 'background_color', 'theme_color', 'icons']);
    expect($content['display'])->toBe('standalone');

    // Check icons definition
    expect($content['icons'])->toBeArray();
    $sizes = array_column($content['icons'], 'sizes');
    expect($sizes)->toContain('192x192');
    expect($sizes)->toContain('512x512');

    // Check that physical icon files exist and are not empty
    foreach ($content['icons'] as $icon) {
        $iconPath = public_path(ltrim($icon['src'], '/'));
        expect(file_exists($iconPath))->toBeTrue();
        expect(filesize($iconPath))->toBeGreaterThan(0);
    }
});

test('service worker sw.js exists and has fetch handler for Chrome PWA installability', function () {
    $swPath = public_path('sw.js');
    expect(file_exists($swPath))->toBeTrue();

    $swContent = file_get_contents($swPath);
    expect($swContent)->toContain("addEventListener('fetch'");
});

test('guest layout and authenticated layout include PWA manifest and service worker registration', function () {
    // 1. Guest layout (e.g. login page)
    $responseGuest = $this->get('/login');
    $responseGuest->assertOk();
    $responseGuest->assertSee('rel="manifest"', false);
    $responseGuest->assertSee('manifest.json');
    $responseGuest->assertSee('name="theme-color"', false);
    $responseGuest->assertSee("navigator.serviceWorker.register('/sw.js')", false);

    // 2. Authenticated layout (e.g. dashboard)
    $user = User::factory()->create();
    $responseAuth = $this->actingAs($user)->get('/dashboard');
    $responseAuth->assertOk();
    $responseAuth->assertSee('rel="manifest"', false);
    $responseAuth->assertSee('manifest.json');
    $responseAuth->assertSee('name="theme-color"', false);
    $responseAuth->assertSee("navigator.serviceWorker.register('/sw.js')", false);
});

