<?php

namespace System\File;

/**
 * This class use for uplaod file to server using move_uploaded_file() function,
 * make with easy use and manitens, every one can use and modifi this class to improve performense.
 *
 * @author sonypradana@gmail.com
 */
class UploadFile
{
    /**
     *  File upload status.
     *
     * @var bool
     */
    private $_success = false;

    /**
     * File has excute to upload.
     *
     * @var bool
     */
    private $_isset = false;

    /**
     * Detect test mode.
     *
     * @var bool
     */
    private $_test = false;

    // property file --------------------------------------------

    /** @var string */
    private $file_name;
    /** @var string Original file category */
    private $file_type;
    /** @var string Original file temp location */
    private $file_tmp;
    /** @var int Original file error status code */
    private $file_error;
    /** @var int Original file size in byte */
    private $file_size;
    /** @var string Original file extension */
    private $file_extension;

    // property upload ------------------------------------------

    /** @var string Upload file name (without extention) */
    private $upload_name;
    /** @var string Upload file to save location */
    private $upload_location = '/';
    /** @var array Upload allow file extention */
    private $upload_types    = ['jpg', 'jpeg', 'png'];
    /** @var array Upload allow file mime type */
    private $upload_mime     = ['image/jpg', 'image/jpeg', 'image/png'];
    /** @var int Upload maksimal file size */
    private $upload_size_max = 50000;

    /**
     * Provide error message.
     *
     * @var string
     */
    private $_error_message = '';

    // setter ------------------------------------------------

    /**
     * Set file name (without extention).
     * File name will convert to allow string url.
     *
     * @param string $file_name File name (without extention)
     */
    public function setFileName(string $file_name)
    {
        // file name without extension
        $file_name         = urlencode($file_name);
        $this->upload_name = $file_name;

        return $this;
    }

    /**
     * File to save/upload location (server folder),
     * Warning:: not creat new folder if location not exis.
     *
     * @param string $folder_location Upload file to save location
     */
    public function setFolderLocation(string $folder_location)
    {
        if (!is_dir($folder_location)) {
            throw new \Exception('Folder not founded');
        }

        $this->upload_location = $folder_location;

        return $this;
    }

    /**
     * List allow file extension to upload.
     *
     * @param array $extensions list extention file
     */
    public function setFileTypes(array $extensions)
    {
        $this->upload_types = $extensions;

        return $this;
    }

    /**
     * List allow file mime type to upload.
     *
     * @param array $mimes list mime type file
     */
    public function setMimeTypes(array $mimes)
    {
        $this->upload_mime = $mimes;

        return $this;
    }

    /**
     * Maksimum file size to upload (in byte).
     *
     * @param int $byte maksimum file size upload
     */
    public function setMaxFileSize(int $byte)
    {
        $this->upload_size_max = $byte;

        return $this;
    }

    /**
     * If true, upload determinate using `copy` instance of `move_uploaded_file`.
     *
     * @param bool $mark_upload_test true use copy file
     *
     * @return self
     */
    public function markTest(bool $mark_upload_test)
    {
        $this->_test = $mark_upload_test;

        return $this;
    }

    // getter --------------------------------------------------------------

    /**
     * File Upload status.
     *
     * @return bool True on file upload success
     */
    public function success(): bool
    {
        return $this->_success;
    }

    /**
     * Error message file upload status.
     *
     * @return string Give url file location
     */
    public function getError(): string
    {
        return $this->_error_message;
    }

    /**
     * Get uploaded file name.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * Get uploaded file types.
     *
     * @return array<int, string>
     */
    public function getFileTypes()
    {
        return $this->upload_types;
    }

    /**
     * Creat New file upload to sarver.
     *
     * @param array $file Super global FILE (single array)
     */
    public function __construct(array $file)
    {
        $this->file_name  = $file['name'];
        $this->file_type  = $file['type'];
        $this->file_tmp   = $file['tmp_name'];
        $this->file_error = $file['error'];
        $this->file_size  = $file['size'];
        // random file name by default
        $this->upload_name = uniqid('simpuslerep_'); // file name without extension
        // parse file extention
        $extension            = explode('.', $file['name']);
        $this->file_extension = strtolower(end($extension));
    }

    /**
     * Helper to validate file upload base on configure
     * - cek file error
     * - cek extention / mime (optional)
     * - cek maskimum size.
     *
     * also return error message
     *
     * @return bool True on error found not found
     */
    private function validate(): bool
    {
        // cek file error
        $file_error = $this->file_error === 4 ? true : false;
        if ($file_error) {
            $this->_error_message = 'no file upload';

            return false;
        }

        // cek file type (upload_type must set)
        $extensio_error = in_array($this->file_extension, $this->upload_types) ? false : true;
        if ($extensio_error) {
            $this->_error_message = 'file type not support';

            return false;
        }

        // cek mime type (upload_mime must set)
        $mime_error = in_array($this->file_type, $this->upload_mime) ? false : true;
        if ($mime_error) {
            $this->_error_message = 'file type not support';

            return false;
        }

        // cek file size
        $is_size_error = $this->file_size > $this->upload_size_max ? true : false;
        if ($is_size_error) {
            $this->_error_message = 'file size too large';

            return false;
        }

        $this->_error_message = 'success';

        return true;
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string File location on success upload file, sting empety when unsuccess upload
     */
    public function upload(): string
    {
        // isset property, enable when data has been validate
        $this->_isset = true;

        if (!$this->validate()) {
            return '';
        }

        $destination =  $this->upload_location . $this->upload_name . '.' . $this->file_extension;

        if ($this->_test && copy($this->file_tmp, $destination)) {
            $this->_success = true;

            return $destination;
        }

        if (!$this->_test && move_uploaded_file($this->file_tmp, $destination)) {
            $this->_success = true;

            return $destination;
        }

        return '';
    }

    /**
     * Helper to delete file if needed.
     *
     * @return bool True on succes deleted file
     */
    public function delete(string $url): bool
    {
        return file_exists($url)
            ? unlink($url)
            : false;
    }

    /**
     * Helper to creat new folder if needed.
     *
     * @return bool True on succes created folder
     */
    public function creatFolder(string $path): bool
    {
        return !file_exists($path)
            ? mkdir($path, 0777, true)
            : false;
    }

    /**
     * True jika class di contruc.
     */
    public function __isset($name): bool
    {
        return $this->_isset;
    }

    public function get()
    {
        $destination =  $this->upload_location . $this->upload_name . '.' . $this->file_extension;

        if (!$this->_success) {
            throw new \Exception('File not uploaded');
        }

        return file_get_contents($destination);
    }
}
