<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    /**
     * Check if installation is already completed
     */
    public function checkInstallation(Request $request)
    {
        try {
            // Check if database connection exists
            DB::connection()->getPdo();

            // Check if users table exists and has data
            if (Schema::hasTable('users')) {
                $userCount = User::count();
                if ($userCount > 0) {
                    return response()->json([
                        'installed' => true,
                        'message' => 'Application is already installed'
                    ]);
                }
            }

            return response()->json([
                'installed' => false,
                'message' => 'Application is not installed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'installed' => false,
                'message' => 'Database connection not configured'
            ]);
        }
    }

    /**
     * Check database connection
     */
    public function checkDatabase(Request $request)
    {
        try {
            $host = $request->input('host', env('DB_HOST', '127.0.0.1'));
            $port = $request->input('port', env('DB_PORT', '3306'));
            $database = $request->input('database', env('DB_DATABASE', ''));
            $username = $request->input('username', env('DB_USERNAME', ''));
            $password = $request->input('password', env('DB_PASSWORD', ''));

            // If a database name is provided, try to create it if it doesn't exist,
            // so users don't see "Unknown database" errors during installation.
            if (! empty($database)) {
                $this->createDatabaseIfNotExists($host, (string) $port, $database, $username, $password);
            }

            // Test connection
            config([
                'database.connections.mysql.host' => $host,
                'database.connections.mysql.port' => $port,
                'database.connections.mysql.database' => $database,
                'database.connections.mysql.username' => $username,
                'database.connections.mysql.password' => $password,
            ]);

            DB::purge('mysql');
            DB::connection('mysql')->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Database connection successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create the database if it does not exist (connects to MySQL server without selecting a database).
     * This only touches the installation flow and does not change core application logic.
     */
    private function createDatabaseIfNotExists(string $host, string $port, string $database, string $username, ?string $password): void
    {
        if ($database === '') {
            return;
        }

        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";

        $pdo = new \PDO($dsn, $username, $password ?? '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $safeName = '`' . str_replace('`', '``', $database) . '`';
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$safeName}");
    }

    /**
     * Run database migrations
     */
    public function runMigrations(Request $request)
    {
        try {
            // Configure the connection from the request first so we fail fast if credentials are bad
            if ($request->has('host')) {
                $host = $request->input('host', env('DB_HOST', '127.0.0.1'));
                $port = $request->input('port', env('DB_PORT', '3306'));
                $database = $request->input('database', env('DB_DATABASE', ''));
                $username = $request->input('username', env('DB_USERNAME', ''));
                $password = $request->input('password', env('DB_PASSWORD', ''));

                config([
                    'database.connections.mysql.host' => $host,
                    'database.connections.mysql.port' => $port,
                    'database.connections.mysql.database' => $database,
                    'database.connections.mysql.username' => $username,
                    'database.connections.mysql.password' => $password,
                ]);

                DB::purge('mysql');
            }

            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            $output = Artisan::output();

            // Only persist DB settings to .env after successful migrations
            if ($request->has('host')) {
                $this->updateEnvFile($request);
            }

            return response()->json([
                'success' => true,
                'message' => 'Database migrations completed successfully',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create super admin user
     */
    public function createSuperAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        try {
            DB::beginTransaction();

            // Get or create super admin role
            $superAdminRole = Role::where('slug', 'super_admin')->first();

            if (!$superAdminRole) {
                $superAdminRole = Role::create([
                    'name' => 'Super Admin',
                    'slug' => 'super_admin',
                    'description' => 'Super Administrator with full system access'
                ]);
            }

            // Create super admin user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $superAdminRole->id,
                'status' => 'active'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Super Admin created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create super admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete installation
     */
    public function completeInstallation(Request $request)
    {
        try {
            // Create installation lock file
            $lockFile = storage_path('app/installed.lock');
            File::put($lockFile, date('Y-m-d H:i:s'));

            // Optionally run seeders
            if ($request->input('seed_data', false)) {
                Artisan::call('db:seed', ['--force' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete installation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update .env file with database credentials
     */
    private function updateEnvFile(Request $request)
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            // Copy from .env.example if .env doesn't exist
            $examplePath = base_path('.env.example');
            if (File::exists($examplePath)) {
                File::copy($examplePath, $envPath);
            } else {
                // Create basic .env file
                File::put($envPath, 'APP_NAME=Laravel' . PHP_EOL . 'APP_ENV=local' . PHP_EOL);
            }
        }

        $envContent = File::get($envPath);

        $updates = [
            'DB_HOST' => $request->input('host', env('DB_HOST')),
            'DB_PORT' => $request->input('port', env('DB_PORT', '3306')),
            'DB_DATABASE' => $request->input('database', env('DB_DATABASE')),
            'DB_USERNAME' => $request->input('username', env('DB_USERNAME')),
            'DB_PASSWORD' => $request->input('password', env('DB_PASSWORD')),
        ];

        foreach ($updates as $key => $value) {
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
            $replacement = $key . '=' . $value;

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= PHP_EOL . $replacement;
            }
        }

        File::put($envPath, $envContent);

        // Clear config cache
        Artisan::call('config:clear');
    }
}
