<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
        {--email= : Administrator email address}
        {--password= : Administrator password (min 12 chars). If omitted, a random one is generated and displayed.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an administrator account (production-safe; no default credentials).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?? $this->ask('Administrator email address');

        $password = $this->option('password') ?? Str::password(16);
        $generated = $this->option('password') === null;

        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => 'Administrator',
            'email' => strtolower($email),
            'password' => Hash::make($password),
            'wallet_address' => '0x'.Str::random(16),
            'role' => User::ROLE_ADMIN,
        ]);

        $this->info("Administrator created: {$user->email}");
        if ($generated) {
            $this->warn('Generated password (store it securely, it will not be shown again): '.$password);
        }

        return self::SUCCESS;
    }
}
