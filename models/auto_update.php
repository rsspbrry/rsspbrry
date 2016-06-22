<?php

namespace Model\AutoUpdate;

use ZipArchive;
use DirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Model\Config;

// Get all files of a given directory
function get_files_list($directory)
{
    $exclude_list = array(
        '.git',
        'data',
        'scripts',
        'config.php',
        'rules',
    );

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::SELF_FIRST);
    $files = array();

    while ($it->valid()) {

        if ($it->isFile() && ! is_excluded_path($it->getSubPathname(), $exclude_list)) {
            $files[] = $it->getSubPathname();
        }

        $it->next();
    }

    return $files;
}

// Check if the given path is excluded
function is_excluded_path($path, array $exclude_list)
{
    foreach ($exclude_list as $excluded_path) {

        if (strpos($path, $excluded_path) === 0) {
            return true;
        }
    }

    return false;
}

// Synchronize 2 directories (copy/remove files)
function synchronize($source_directory, $destination_directory)
{
    Config\debug('[SYNCHRONIZE] '.$source_directory.' to '.$destination_directory);

    $src_files = get_files_list($source_directory);
    $dst_files = get_files_list($destination_directory);

    // Remove files
    $remove_files = array_diff($dst_files, $src_files);

    foreach ($remove_files as $file) {

        if ($file !== '.htaccess') {

            $destination_file = $destination_directory.DIRECTORY_SEPARATOR.$file;
            Config\debug('[REMOVE] '.$destination_file);

            if (! @unlink($destination_file)) {
                return false;
            }
        }
    }

    // Overwrite all files
    foreach ($src_files as $file) {

        $directory = $destination_directory.DIRECTORY_SEPARATOR.dirname($file);

        if (! is_dir($directory)) {

            Config\debug('[MKDIR] '.$directory);

            if (! @mkdir($directory, 0755, true)) {
                return false;
            }
        }

        $source_file = $source_directory.DIRECTORY_SEPARATOR.$file;
        $destination_file = $destination_directory.DIRECTORY_SEPARATOR.$file;

        Config\debug('[COPY] '.$source_file.' to '.$destination_file);

        if (! @copy($source_file, $destination_file)) {
            return false;
        }
    }

    return true;
}

// Download and unzip the archive
function uncompress_archive($url, $download_directory = AUTO_UPDATE_DOWNLOAD_DIRECTORY, $archive_directory = AUTO_UPDATE_ARCHIVE_DIRECTORY)
{
    $archive_file = $download_directory.DIRECTORY_SEPARATOR.'update.zip';

    Config\debug('[DOWNLOAD] '.$url);

    if (($data = @file_get_contents($url)) === false) {
        return false;
    }

    if (@file_put_contents($archive_file, $data) === false) {
        return false;
    }

    Config\debug('[UNZIP] '.$archive_file);

    $zip = new ZipArchive;

    if (! $zip->open($archive_file)) {
        return false;
    }

    $zip->extractTo($archive_directory);
    $zip->close();

    return true;
}

// Remove all files for a given directory
function cleanup_directory($directory)
{
    Config\debug('[CLEANUP] '.$directory);

    $dir = new DirectoryIterator($directory);

    foreach ($dir as $fileinfo) {

        if (! $fileinfo->isDot()) {

            $filename = $fileinfo->getRealPath();

            if ($fileinfo->isFile()) {
                \Model\Config\debug('[REMOVE] '.$filename);
                @unlink($filename);
            }
            else {
                cleanup_directory($filename);
                @rmdir($filename);
            }
        }
    }
}

// Cleanup all temporary directories
function cleanup_directories()
{
    cleanup_directory(AUTO_UPDATE_DOWNLOAD_DIRECTORY);
    cleanup_directory(AUTO_UPDATE_ARCHIVE_DIRECTORY);
    cleanup_directory(AUTO_UPDATE_BACKUP_DIRECTORY);
}

// Find the archive directory name
function find_archive_root($base_directory = AUTO_UPDATE_ARCHIVE_DIRECTORY)
{
    $directory = '';
    $dir = new DirectoryIterator($base_directory);

    foreach ($dir as $fileinfo) {
        if (! $fileinfo->isDot() && $fileinfo->isDir()) {
            $directory = $fileinfo->getFilename();
            break;
        }
    }

    if (empty($directory)) {
        Config\debug('[FIND ARCHIVE] No directory found');
        return false;
    }

    $path = $base_directory.DIRECTORY_SEPARATOR.$directory;
    Config\debug('[FIND ARCHIVE] '.$path);

    return $path;
}

// Check if everything is setup correctly
function check_setup()
{
    if (! class_exists('ZipArchive')) die('To use this feature, your PHP installation must be able to uncompress zip files!');

    if (AUTO_UPDATE_DOWNLOAD_DIRECTORY === '') die('The constant AUTO_UPDATE_DOWNLOAD_DIRECTORY is not set!');
    if (AUTO_UPDATE_ARCHIVE_DIRECTORY === '') die('The constant AUTO_UPDATE_ARCHIVE_DIRECTORY is not set!');
    if (AUTO_UPDATE_DOWNLOAD_DIRECTORY === '') die('The constant AUTO_UPDATE_DOWNLOAD_DIRECTORY is not set!');

    if (! is_dir(AUTO_UPDATE_DOWNLOAD_DIRECTORY)) @mkdir(AUTO_UPDATE_DOWNLOAD_DIRECTORY, 0755);
    if (! is_dir(AUTO_UPDATE_ARCHIVE_DIRECTORY)) @mkdir(AUTO_UPDATE_ARCHIVE_DIRECTORY, 0755);
    if (! is_dir(AUTO_UPDATE_BACKUP_DIRECTORY)) @mkdir(AUTO_UPDATE_BACKUP_DIRECTORY, 0755);

    if (! is_writable(AUTO_UPDATE_DOWNLOAD_DIRECTORY)) die('Update directories must be writable by your web server user!');
    if (! is_writable(__DIR__)) die('Source files must be writable by your web server user!');
}

// Update the source code
function execute($url)
{
    check_setup();
    cleanup_directories();

    if (uncompress_archive($url)) {

        $update_directory = find_archive_root();

        if ($update_directory) {

            // Backup first
            if (synchronize(ROOT_DIRECTORY, AUTO_UPDATE_BACKUP_DIRECTORY)) {

                // Update
                if (synchronize($update_directory, ROOT_DIRECTORY)) {
                    cleanup_directories();
                    return true;
                }
                else {
                    // If update failed, rollback
                    synchronize(AUTO_UPDATE_BACKUP_DIRECTORY, ROOT_DIRECTORY);
                }
            }
        }
    }

    return false;
}
