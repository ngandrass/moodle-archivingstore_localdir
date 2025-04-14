<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Driver for storing archive data inside a directory on the local filesystem
 *
 * @package     archivingstore_localdir
 * @copyright   2025 Niels Gandraß <niels@gandrass.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace archivingstore_localdir;

use local_archiving\driver\store\file_handle;
use local_archiving\exception\storage_exception;

// @codingStandardsIgnoreFile
defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore


/**
 * Driver for storing archive data inside a directory on the local filesystem
 */
class archivingstore extends \local_archiving\driver\store\archivingstore {

    // FIXME: Remove. This is for development only. Needs to be put into a proper setting.
    public const LOCAL_DIR = '/app/moodledata/temp/archivingstore_localdir';

    /**
     * @inheritDoc archivingstore_base::get_name()
     */
    public static function get_name(): string {
        return get_string('pluginname', 'archivingstore_localdir');
    }

    /**
     * @inheritDoc
     */
    public static function get_plugname(): string {
        return 'localdir';
    }

    /**
     * @inheritDoc
     */
    public function is_available(): bool {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function get_free_bytes(): int {
        // TODO: Implement get_free_bytes() method.
        return 42;
    }

    /**
     * @inheritDoc
     */
    public function store(int $jobid, \stored_file $file, string $path): file_handle {
        // TODO: Implement store() method.

        $handle = file_handle::create(
            $jobid,
            'localdir',
            $file->get_filename(),
            trim($path, '/'),
            $file->get_filesize()
        );

        $abstargetpath = self::LOCAL_DIR.'/'.$handle->filepath;
        if (!is_dir($abstargetpath)) {
            if (!mkdir($abstargetpath, 0777, true)) {
                $handle->destroy();
                throw new storage_exception('filestorefailed', 'local_archiving');
            }
        }
        if (!$file->copy_content_to($abstargetpath.'/'.$file->get_filename())) {
            $handle->destroy();
            throw new storage_exception('filestorefailed', 'local_archiving');
        }

        mtrace('Stored file '.$file->get_filename().' in '.$abstargetpath.'/'.$file->get_filename());
        return $handle;
    }

    /**
     * @inheritDoc
     */
    public function retrieve(file_handle $handle): \stored_file {
        // TODO: Implement retrieve() method.
        throw new storage_exception('notimplemented', 'archivingstore_localdir');
    }

    /**
     * @inheritDoc
     */
    public function delete(file_handle $handle, bool $strict = false): void {
        // TODO: Implement delete() method.

        $filefullpath = self::LOCAL_DIR.'/'.$handle->filepath.'/'.$handle->filename;
        if (!file_exists($filefullpath)) {
            if ($strict) {
                throw new storage_exception('filenotfound', 'error');
            } else {
                return;
            }
        }

        if (!unlink($filefullpath)) {
            throw new storage_exception('filedeletefailed', 'local_archiving');
        }
    }

}
