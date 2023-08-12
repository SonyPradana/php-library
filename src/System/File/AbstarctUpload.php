<?php

declare(strict_types=1);

namespace System\File;

/**
 * This class use for uplaod file to server using move_uploaded_file() function,
 * make with easy use and manitens, every one can use and modifi this class to improve performense.
 *
 * @author sonypradana@gmail.com
 */
abstract class AbstarctUpload
{
    /**
     * Cath files form upload file.
     *
     * @var array<string, array<int, string>|string>
     */
    protected $_files;

    /**
     *  File upload status.
     *
     * @var bool
     */
    protected $_success = false;

    /**
     * File has excute to upload.
     *
     * @var bool
     */
    protected $_isset = false;

    /**
     * Detect test mode.
     *
     * @var bool
     */
    protected $_test = false;

    /**
     * Detect single or multy upload files.
     *
     * @var bool True if multy file upload
     */
    protected $_is_multy = false;

    // property file --------------------------------------------

    /** @var string[] */
    protected $file_name;
    /** @var string[] Original file category */
    protected $file_type;
    /** @var string[] Original file temp location */
    protected $file_tmp;
    /** @var int[] Original file error status code */
    protected $file_error;
    /** @var int[] Original file size in byte */
    protected $file_size;
    /** @var string[] Original file extension */
    protected $file_extension;

    // property upload ------------------------------------------

    /** @var string Upload file name (without extention) */
    protected $upload_name;
    /** @var string Upload file to save location */
    protected $upload_location = '/';
    /** @var array<int, string> Upload allow file extention */
    protected $upload_types    = ['jpg', 'jpeg', 'png'];
    /** @var array<int, string> Upload allow file mime type */
    protected $upload_mime     = ['image/jpg', 'image/jpeg', 'image/png'];
    /** @var int Upload maksimal file size */
    protected $upload_size_max = 50000;

    /**
     * Provide error message.
     *
     * @var string
     */
    protected $_error_message = '';

    // setter ------------------------------------------------

    /**
     * Set file name (without extention).
     * File name will convert to allow string url.
     *
     * @param string $file_name File name (without extention)
     */
    abstract public function setFileName(string $file_name): self;

    /**
     * File to save/upload location (server folder),
     * Warning:: not creat new folder if location not exis.
     *
     * @param string $folder_location Upload file to save location
     */
    abstract public function setFolderLocation(string $folder_location): self;

    /**
     * List allow file extension to upload.
     *
     * @param array<int, string> $extensions list extention file
     */
    abstract public function setFileTypes(array $extensions): self;

    /**
     * List allow file mime type to upload.
     *
     * @param array<int, string> $mimes list mime type file
     */
    abstract public function setMimeTypes(array $mimes): self;

    /**
     * Maksimum file size to upload (in byte).
     *
     * @param int $byte maksimum file size upload
     */
    abstract public function setMaxFileSize(int $byte): self;

    /**
     * If true, upload determinate using `copy` instance of `move_uploaded_file`.
     *
     * @param bool $mark_upload_test true use copy file
     */
    abstract public function markTest(bool $mark_upload_test): self;

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
     * @param array<string, string|int|array<string>|array<int>> $files Super global FILE (single array)
     */
    public function __construct($files)
    {
        // random files name by default
        $this->upload_name = uniqid('uploaded_'); // files name without extension

        $this->_files = $files;
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
    protected function validate(): bool
    {
        // cek file error
        foreach ($this->file_error as $error) {
            $file_error = $error === 4 ? true : false;
            if ($file_error) {
                $this->_error_message = 'no file upload';

                return false;
            }
        }

        // cek file type (upload_type must set)
        foreach ($this->file_extension as $extension) {
            $extensio_error = in_array($extension, $this->upload_types) ? false : true;
            if ($extensio_error) {
                $this->_error_message = 'file type not support';

                return false;
            }
        }

        // cek mime type (upload_mime must set)
        foreach ($this->file_type as $type) {
            $mime_error = in_array($type, $this->upload_mime) ? false : true;
            if ($mime_error) {
                $this->_error_message = 'file type not support';

                return false;
            }
        }

        // cek file size
        foreach ($this->file_size as $size) {
            $is_size_error = $size > $this->upload_size_max ? true : false;
            if ($is_size_error) {
                $this->_error_message = 'file size too large';

                return false;
            }
        }

        $this->_error_message = 'success';

        return true;
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string[] File location on success upload file, sting empety when unsuccess upload
     */
    protected function stream()
    {
        // isset property, enable when data has been validate
        $this->_isset = true;
        $destinations = [];

        if (!$this->validate()) {
            return $destinations;
        }

        if ($this->_test) {
            foreach ($this->file_extension as $key => $extension) {
                $surfix         = $this->_is_multy ? $key : '';
                $destination    =  $this->upload_location . $this->upload_name . $surfix . '.' . $extension;
                $this->_success = copy($this->file_tmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }

        if ($this->_test === false) {
            foreach ($this->file_extension as $key => $extension) {
                $surfix         = $this->_is_multy ? $key : '';
                $destination    =  $this->upload_location . $this->upload_name . $surfix . '.' . $extension;
                $this->_success = move_uploaded_file($this->file_tmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }
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
}
