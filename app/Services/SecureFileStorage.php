<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Single entry point for sensitive tenant files (design files, payment proofs).
 *
 * - Files are written to a private disk that is never exposed by the web server.
 * - Files are only delivered through controllers that perform authorization first.
 * - Files uploaded before private storage existed are still readable from the
 *   legacy public disk until `php artisan orderflow:secure-files` migrates them.
 */
class SecureFileStorage
{
    /** MIME types that are safe to render inline in the browser. */
    private const INLINE_SAFE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'application/pdf',
    ];

    public function privateDiskName(): string
    {
        return (string) config('orderflow.storage.private_disk', 'local');
    }

    public function legacyDiskName(): string
    {
        return (string) config('orderflow.storage.legacy_disk', 'public');
    }

    public function privateDisk(): FilesystemAdapter
    {
        return Storage::disk($this->privateDiskName());
    }

    public function legacyDisk(): FilesystemAdapter
    {
        return Storage::disk($this->legacyDiskName());
    }

    /**
     * Store an upload on the private disk using a random, non-guessable file name.
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($this->normalizeDirectory($directory), $this->privateDiskName());

        if ($path === false) {
            throw new \RuntimeException('Unable to store the uploaded file.');
        }

        return $path;
    }

    public function exists(?string $path): bool
    {
        return $this->resolveDisk($path) !== null;
    }

    /**
     * Stream a stored file. Callers MUST authorize the request before calling this.
     */
    public function response(string $path, string $downloadName, bool $preferInline = false): StreamedResponse
    {
        $disk = $this->resolveDisk($path);
        abort_if($disk === null, 404);

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $inline = $preferInline && in_array($mimeType, self::INLINE_SAFE_MIME_TYPES, true);

        $headers = [
            'Content-Type' => $inline ? $mimeType : 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
            // Neutralises any active content even if a malicious file slips through.
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
        ];

        return $disk->response($path, $this->safeFileName($downloadName), $headers, $inline ? 'inline' : 'attachment');
    }

    /**
     * Delete a file from both the private and legacy disks.
     */
    public function delete(?string $path): void
    {
        if (! $this->isSafePath($path)) {
            return;
        }

        $this->privateDisk()->delete($path);
        $this->legacyDisk()->delete($path);
    }

    /**
     * Move a legacy public file into private storage, keeping the same relative path.
     *
     * @return bool true when the file was moved
     */
    public function migrateFromLegacy(string $path): bool
    {
        if (! $this->isSafePath($path)) {
            return false;
        }

        $legacy = $this->legacyDisk();
        $private = $this->privateDisk();

        if (! $legacy->exists($path)) {
            return false;
        }

        if (! $private->exists($path)) {
            $stream = $legacy->readStream($path);
            if ($stream === null || $stream === false) {
                return false;
            }

            try {
                if (! $private->writeStream($path, $stream)) {
                    return false;
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }

        // Only remove the public copy once the private copy is confirmed.
        if ($private->exists($path) && $private->size($path) === $legacy->size($path)) {
            $legacy->delete($path);

            return true;
        }

        return false;
    }

    private function resolveDisk(?string $path): ?FilesystemAdapter
    {
        if (! $this->isSafePath($path)) {
            return null;
        }

        foreach ([$this->privateDisk(), $this->legacyDisk()] as $disk) {
            if ($disk->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    private function isSafePath(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        $normalized = str_replace('\\', '/', $path);

        return ! str_starts_with($normalized, '/')
            && ! str_contains($normalized, "\0")
            && ! in_array('..', explode('/', $normalized), true);
    }

    private function normalizeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');

        if (! $this->isSafePath($directory)) {
            throw new \InvalidArgumentException('Invalid storage directory.');
        }

        return $directory;
    }

    private function safeFileName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F"]/u', '', $name) ?? '';

        return $name !== '' ? Str::limit($name, 180, '') : 'file';
    }
}
