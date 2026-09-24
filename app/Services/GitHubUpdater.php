<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use ZipArchive;

class GitHubUpdater
{
    public function latest(): array
    {
        $request = Http::acceptJson()->withHeaders(['User-Agent' => 'Ginhawa-Updater/'.config('ginhawa.version')])->timeout(20);
        if ($token = config('ginhawa.github_token')) {
            $request = $request->withToken($token);
        }
        $response = $request->get('https://api.github.com/repos/'.config('ginhawa.github_repository').'/releases/latest');
        if ($response->status() === 404) {
            throw new RuntimeException('GitHub returned 404. Check the repository name, visibility, and that a published release exists.');
        }
        $response->throw();
        $release = $response->json();
        if (empty($release['tag_name']) || empty($release['zipball_url'])) {
            throw new RuntimeException('The GitHub response does not contain a usable published release.');
        }
        $release['version'] = ltrim($release['tag_name'], 'vV');
        $release['is_newer'] = version_compare($release['version'], config('ginhawa.version'), '>');
        return $release;
    }

    public function install(array $release): void
    {
        if (empty($release['zipball_url']) || empty($release['version'])) {
            throw new RuntimeException('Invalid release metadata. Check for updates again.');
        }

        $updateDirectory = storage_path('app/updates');
        File::ensureDirectoryExists($updateDirectory);
        $archive = $updateDirectory.'/release-'.preg_replace('/[^A-Za-z0-9._-]/', '-', $release['version']).'.zip';
        $extractDirectory = $updateDirectory.'/extract-'.uniqid();
        $request = Http::withHeaders(['User-Agent' => 'Ginhawa-Updater/'.config('ginhawa.version')])->timeout(120);
        if ($token = config('ginhawa.github_token')) {
            $request = $request->withToken($token);
        }
        $request->withOptions(['sink' => $archive])->get($release['zipball_url'])->throw();

        $zip = new ZipArchive();
        if ($zip->open($archive) !== true) {
            throw new RuntimeException('The GitHub release archive could not be opened.');
        }
        File::ensureDirectoryExists($extractDirectory);
        $zip->extractTo($extractDirectory);
        $zip->close();

        $entries = array_values(array_filter(File::directories($extractDirectory), fn ($path) => basename($path) !== '.' && basename($path) !== '..'));
        $source = count($entries) === 1 ? $entries[0] : $extractDirectory;
        $protected = ['.env', 'vendor', 'storage', 'bootstrap/cache', 'database/database.sqlite'];
        foreach (File::allFiles($source) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            if ($this->isProtected($relative, $protected)) {
                continue;
            }
            $destination = base_path($relative);
            File::ensureDirectoryExists(dirname($destination));
            File::copy($file->getPathname(), $destination);
        }

        File::put(base_path('VERSION'), $release['version'].PHP_EOL);
        Artisan::call('config:clear');
        File::deleteDirectory($extractDirectory);
        File::delete($archive);
    }

    private function isProtected(string $path, array $protected): bool
    {
        return in_array($path, $protected, true) || collect($protected)->contains(fn ($item) => str_starts_with($path, trim($item, '/').'/'));
    }
}