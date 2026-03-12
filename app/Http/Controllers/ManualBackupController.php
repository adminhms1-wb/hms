<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ManualBackupController extends Controller
{
    public function getTables()
    {
        $tables = DB::select('SHOW TABLES');
        $tableName = 'Tables_in_' . DB::getDatabaseName();
        $tableList = array_map(function($table) use ($tableName) {
            return $table->$tableName;
        }, $tables);

        // Filter out migrations and system tables
        $tableList = array_filter($tableList, function($table) {
            return !in_array($table, ['migrations', 'failed_jobs', 'password_resets', 'personal_access_tokens']);
        });

        return response()->json(['tables' => array_values($tableList)]);
    }

    public function index()
    {
        $backups = Backup::where('type', 'manual')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($backup) {
                return $backup != null && $backup->id != null;
            })
            ->values();

        return response()->json(['backups' => $backups]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:full,tables',
            'name' => 'nullable|string|max:255',
            'tables' => 'nullable|array',
        ]);

        $backup = Backup::create([
            'name' => $validated['name'] ?? 'Manual Backup ' . Carbon::now()->format('Y-m-d H:i:s'),
            'type' => 'manual',
            'status' => 'pending',
            'file_path' => '',
            'backup_date' => now(),
            'tables' => $validated['type'] === 'tables' ? json_encode($validated['tables']) : null,
        ]);

        // Run backup in background
        try {
            $this->performBackup($backup, $validated);
            $backup->update(['status' => 'success']);
        } catch (\Exception $e) {
            $backup->update(['status' => 'failed']);
            return response()->json(['message' => 'Backup failed: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Backup created successfully', 'backup' => $backup], 201);
    }

    private function performBackup($backup, $data)
    {
        $fileName = 'backup_' . $backup->id . '_' . time() . '.sql';
        $filePath = storage_path('app/backups/' . $fileName);

        // Ensure directory exists
        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        // Generate SQL dump using Laravel's database dump
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port', 3306);

        // Find mysqldump path (Windows/XAMPP specific)
        $mysqldumpPath = $this->findMysqldumpPath();
        
        if (!$mysqldumpPath) {
            throw new \Exception('mysqldump command not found. Please ensure MySQL is installed and mysqldump is in your PATH.');
        }

        // Build command with proper escaping
        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers %s',
            $mysqldumpPath,
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbName)
        );

        if ($data['type'] === 'tables' && !empty($data['tables'])) {
            $command .= ' ' . implode(' ', array_map('escapeshellarg', $data['tables']));
        }

        // Use proper redirection for Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command .= ' > ' . escapeshellarg($filePath) . ' 2>&1';
        } else {
            $command .= ' > ' . escapeshellarg($filePath) . ' 2>&1';
        }

        exec($command, $output, $returnVar);

        // Check if file was created and has content
        if ($returnVar !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
            $errorMsg = !empty($output) ? implode("\n", $output) : 'Unknown error occurred';
            // Clean up empty file if it exists
            if (file_exists($filePath) && filesize($filePath) === 0) {
                @unlink($filePath);
            }
            throw new \Exception('Backup command failed: ' . $errorMsg);
        }

        $fileSize = filesize($filePath);
        $backup->update([
            'file_path' => $filePath,
            'size' => $fileSize,
            'status' => 'success'
        ]);
    }

    private function findMysqldumpPath()
    {
        // Try common XAMPP paths on Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Get XAMPP installation directory from various possible locations
            $possibleBasePaths = [
                'F:\\xampp', // Based on workspace path F:\xampp\htdocs\Lr\hms
                'C:\\xampp',
                'D:\\xampp',
                'E:\\xampp',
                'C:\\Program Files\\xampp',
                'C:\\Program Files (x86)\\xampp',
            ];
            
            // Extract XAMPP path from current script location if it contains xampp
            $currentPath = __DIR__;
            if (stripos($currentPath, 'xampp') !== false) {
                // Extract path up to and including xampp
                $xamppPos = stripos($currentPath, 'xampp');
                if ($xamppPos !== false) {
                    $xamppBasePath = substr($currentPath, 0, $xamppPos + 5); // Include 'xampp' (5 chars)
                    if (!in_array($xamppBasePath, $possibleBasePaths)) {
                        array_unshift($possibleBasePaths, $xamppBasePath); // Add to beginning for priority
                    }
                }
            }
            
            // Also try to get from base_path() which might contain xampp
            $basePath = base_path();
            if (stripos($basePath, 'xampp') !== false) {
                $xamppPos = stripos($basePath, 'xampp');
                if ($xamppPos !== false) {
                    $xamppBasePath = substr($basePath, 0, $xamppPos + 5);
                    if (!in_array($xamppBasePath, $possibleBasePaths)) {
                        array_unshift($possibleBasePaths, $xamppBasePath);
                    }
                }
            }
            
            $possiblePaths = [];
            foreach ($possibleBasePaths as $basePath) {
                $possiblePaths[] = $basePath . '\\mysql\\bin\\mysqldump.exe';
                $possiblePaths[] = $basePath . '\\mysql\\bin\\mysqldump';
            }
            
            // Add direct executable names
            $possiblePaths[] = 'mysqldump.exe';
            $possiblePaths[] = 'mysqldump';
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            
            // Try to find it in PATH using where command
            $output = [];
            exec('where mysqldump 2>nul', $output, $returnVar);
            if ($returnVar === 0 && !empty($output) && is_array($output)) {
                $foundPath = trim($output[0]);
                if (file_exists($foundPath)) {
                    return $foundPath;
                }
            }
            
            // Try using where.exe explicitly
            $output = [];
            exec('where.exe mysqldump 2>nul', $output, $returnVar);
            if ($returnVar === 0 && !empty($output) && is_array($output)) {
                $foundPath = trim($output[0]);
                if (file_exists($foundPath)) {
                    return $foundPath;
                }
            }
        } else {
            // Linux/Unix
            $output = [];
            exec('which mysqldump 2>/dev/null', $output, $returnVar);
            if ($returnVar === 0 && !empty($output) && is_array($output)) {
                $foundPath = trim($output[0]);
                if (file_exists($foundPath)) {
                    return $foundPath;
                }
            }
            
            // Try common Linux paths
            $linuxPaths = [
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                '/opt/lampp/bin/mysqldump', // XAMPP for Linux
            ];
            
            foreach ($linuxPaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
        
        return null;
    }

    public function download($id)
    {
        $backup = Backup::findOrFail($id);
        
        if (!file_exists($backup->file_path)) {
            return response()->json(['message' => 'Backup file not found'], 404);
        }

        return response()->download($backup->file_path, basename($backup->file_path));
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);
        
        if (file_exists($backup->file_path)) {
            unlink($backup->file_path);
        }
        
        $backup->delete();

        return response()->json(['message' => 'Backup deleted successfully']);
    }
}
