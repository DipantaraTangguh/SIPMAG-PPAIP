<?php

namespace Tests\Unit;

use App\Support\StoredFilePath;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StoredFilePathTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = storage_path('framework/testing/stored-files');
        File::deleteDirectory($this->root);
        File::ensureDirectoryExists($this->root.'/transkrip');
        File::put($this->root.'/transkrip/example.pdf', 'safe file');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);

        parent::tearDown();
    }

    public function test_it_resolves_an_existing_file_inside_the_storage_root(): void
    {
        $this->assertSame(
            realpath($this->root.'/transkrip/example.pdf'),
            StoredFilePath::resolve($this->root, 'transkrip/example.pdf'),
        );
    }

    public function test_it_rejects_path_traversal_and_absolute_paths(): void
    {
        $this->assertNull(StoredFilePath::resolve($this->root, '../outside.pdf'));
        $this->assertNull(StoredFilePath::resolve($this->root, 'transkrip/../../outside.pdf'));
        $this->assertNull(StoredFilePath::resolve($this->root, '..\\outside.pdf'));
        $this->assertNull(StoredFilePath::resolve($this->root, '/etc/passwd'));
        $this->assertNull(StoredFilePath::resolve($this->root, 'C:\\Windows\\system.ini'));
    }

    public function test_it_rejects_missing_files_and_directories(): void
    {
        $this->assertNull(StoredFilePath::resolve($this->root, 'transkrip/missing.pdf'));
        $this->assertNull(StoredFilePath::resolve($this->root, 'transkrip'));
    }

    public function test_it_rejects_a_symlink_that_escapes_the_storage_root(): void
    {
        $outsideFile = storage_path('framework/testing/outside-stored-file.txt');
        File::put($outsideFile, 'outside file');
        symlink($outsideFile, $this->root.'/transkrip/linked.pdf');

        try {
            $this->assertNull(StoredFilePath::resolve($this->root, 'transkrip/linked.pdf'));
        } finally {
            File::delete($this->root.'/transkrip/linked.pdf');
            File::delete($outsideFile);
        }
    }
}
