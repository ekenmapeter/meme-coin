<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_user_can_update_name_email_and_wallet(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'wallet_address' => '0xNewWallet1234567890',
            'current_password' => 'password',
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('0xNewWallet1234567890', $user->wallet_address);
    }

    public function test_profile_update_rejects_wrong_current_password(): void
    {
        $user = $this->createUser();
        $originalName = $user->name;

        $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'Hacker',
            'email' => $user->email,
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertSame($originalName, $user->refresh()->name);
    }

    public function test_email_must_be_unique(): void
    {
        $user = $this->createUser();
        $other = $this->createUser(['email' => 'taken@example.com']);

        $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => 'taken@example.com',
            'current_password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-secure-pass-123',
            'password_confirmation' => 'new-secure-pass-123',
        ])->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-secure-pass-123', $user->refresh()->password));
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('profile.password'), [
            'current_password' => 'nope',
            'password' => 'new-secure-pass-123',
            'password_confirmation' => 'new-secure-pass-123',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_user_can_upload_avatar(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->post(route('profile.avatar'), [
            'avatar' => UploadedFile::fake()->image('avatar.png', 100, 100),
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertFileExists(public_path($user->avatar));

        @unlink(public_path($user->avatar));
    }
}
