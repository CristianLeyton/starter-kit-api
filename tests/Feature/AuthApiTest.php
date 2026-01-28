<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'Usuario registrado. Revisa tu email para verificar.'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'username' => 'johndoe',
        ]);
    }

    /** @test */
    public function it_can_login_with_email()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'login' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user',
                    'token',
                ]);
    }

    /** @test */
    public function it_can_login_with_username()
    {
        $user = User::factory()->create([
            'username' => 'johndoe',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'login' => 'johndoe',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user',
                    'token',
                ]);
    }

    /** @test */
    public function it_cannot_login_without_verified_email()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'login' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'message' => 'Email no verificado',
                ]);
    }

    /** @test */
    public function it_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Sesión cerrada',
                ]);
    }
}
