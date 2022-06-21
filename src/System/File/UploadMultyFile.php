<?php

declare(strict_types=1);

namespace System\File;

use System\File\Exceptions\FileNotExists;
use System\File\Exceptions\FileNotUploaded;
use System\File\Exceptions\FolderNotExists;

/** {@inheritDoc} */
final class UploadMultyFile extends AbstarctUpload
{
    /**
     * {@inheritDoc}
     */
    public function setFileName(string $file_name): self
    {
        // file name without extension
        $file_name         = urlencode($file_name);
        $this->upload_name = $file_name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFolderLocation(string $folder_location): self
    {
        if (!is_dir($folder_location)) {
            throw new FolderNotExists($folder_location);
        }

        $this->upload_location = $folder_location;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFileTypes(array $extensions): self
    {
        $this->upload_types = $extensions;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMimeTypes(array $mimes): self
    {
        $this->upload_mime = $mimes;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaxFileSize(int $byte): self
    {
        $this->upload_size_max = $byte;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function markTest(bool $mark_upload_test): self
    {
        $this->_test = $mark_upload_test;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __construct(array $files)
    {
        parent::__construct($files);

        if (is_array($files['name'])) {
            $this->file_name  = $files['name'];
            $this->file_type  = $files['type'];
            $this->file_tmp   = $files['tmp_name'];
            $this->file_error = $files['error'];
            $this->file_size  = $files['size'];
            // parse file extention
            foreach ($files['name'] as $name) {
                $extension              = explode('.', $name);
                $this->file_extension[] = strtolower(end($extension));
            }
        } else {
            $this->file_name[]  = $files['name'];
            $this->file_type[]  = $files['type'];
            $this->file_tmp[]   = $files['tmp_name'];
            $this->file_error[] = $files['error'];
            $this->file_size[]  = $files['size'];
            // parse files extention
            $extension              = explode('.', $files['name']);
            $this->file_extension[] = strtolower(end($extension));
        }

        $this->_is_multy = true;
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string[] File location on success upload file, sting empety when unsuccess upload
     */
    public function uploads()
    {
        return $this->stream();
    }

    /**
     * Get all uploaded files content.
     *
     * @return string[]
     */
    public function getAll()
    {
        if (!$this->_success) {
            throw new FileNotUploaded();
        }

        $contents = [];

        foreach ($this->file_extension as $key => $extension) {
            $destination    =  $this->upload_location . $this->upload_name . $key . '.' . $extension;
            $content        = file_get_contents($destination);

            if (false === $content) {
                throw new FileNotExists($destination);
            }
            $contents[] = $content;
        }

        return $contents;
    }
}
