<?php
declare(strict_types=1);

namespace Domain\File\UseCase;

use Domain\File\Aggregate\File;
use Domain\File\Aggregate\FileCollection;
use Domain\File\Infrastructure\Repository\Bitrix\FileRepository;
use Domain\File\Infrastructure\Repository\FileRepositoryInterface;
use Domain\Service\ServiceInterface;
use Domain\UseCase\AbstractManager;
use Exception;
use InvalidArgumentException;

/**
 * Service for working with File Aggregate. Singleton Pattern.
 * Uploading a file is a responsibility of FileRepository.
 */
class FileManager extends AbstractManager implements ServiceInterface
{
    private ?ServiceInterface $service;
    
    /** @var FileRepositoryInterface  */
    private FileRepositoryInterface $repository;
    
    /** @var string Files upload Directory */
    private string $uploadDir = '';
    
    /**
     * @param FileRepositoryInterface|null $repository
     * @throws Exception
     */
    public function __construct(FileRepositoryInterface $repository = null, ?ServiceInterface $service = null)
    {
        $this->service = $service ?? $this;
        $this->repository = $repository ?? new FileRepository($this->service); // @fixme use DI
        $this->setUploadDir($_SERVER['DOCUMENT_ROOT'] . '/upload/files'); // @fixme
        
        parent::__construct();
    
        $this->filter   = $this->repository->filter;
        $this->fields   = $this->repository->fields;
        $this->sort     = $this->repository->sort;
        $this->limit    = $this->repository->limit;
    }
    
    /**
     * @param File $file
     * @return File
     * @throws Exception
     */
    public function persist(File $file): File
    {
        return $this->repository->persist($file);
    }
    
    /**
     * @param array $userFile Файлы из массива $_FILES[], например $_FILES['files']
     * @param string[] $descriptions Массив с описаниями. Ключи массива должны соответствовать "файлам"
     * @return ?FileCollection
     * @throws Exception
     */
    public function uploadFiles(array $userFile, array $descriptions = []): ?FileCollection
    {
        $files = new FileCollection();
        
        if ( !isset($userFile['error']) ) {
            return null;
        }
        
        if ( is_array($userFile['error']) ) {
            foreach ( $userFile['error'] as $key => $error ) {
                
                if ( UPLOAD_ERR_NO_FILE === $error ) {
                    continue;
                }
                
                $this->checkUploadError($error);
                
                $files->addItem($this->upload($userFile['tmp_name'][$key],$userFile['name'][$key], ($descriptions[$key] ?? '')));
            }
        } else {
            if ( UPLOAD_ERR_NO_FILE === $userFile['error'] ) {
                return null;
            }
            $this->checkUploadError($userFile['error']);
            $files->addItem($this->upload($userFile['tmp_name'],$userFile['name'], ($descriptions[0] ?? '')));
        }
        
        return $files;
    }
    
    /**
     * @param int $error
     * @return void
     * @throws Exception
     * @link https://www.php.net/manual/en/features.file-upload.errors.php
     */
    private function checkUploadError(int $error): void
    {
        switch ( $error ) {
            case UPLOAD_ERR_OK:
                return;
            case UPLOAD_ERR_INI_SIZE:
                // Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
                throw new Exception('Размер файла превышает максимально допустимый в настройках сервера', UPLOAD_ERR_INI_SIZE);
            case UPLOAD_ERR_FORM_SIZE:
                // Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
                throw new Exception('Размер файла превышает максимально допустимый формой размер', UPLOAD_ERR_FORM_SIZE);
            case UPLOAD_ERR_PARTIAL:
                // Value: 3; The uploaded file was only partially uploaded.
                throw new Exception('Загружаемый файл был получен только частично', UPLOAD_ERR_PARTIAL);
            case UPLOAD_ERR_NO_FILE:
                // Value: 4; No file was uploaded.
                throw new Exception('Файл не был загружен', UPLOAD_ERR_NO_FILE);
            case UPLOAD_ERR_NO_TMP_DIR:
                // Value: 6; Missing a temporary folder.
                throw new Exception('Отсутствует временная папка', UPLOAD_ERR_NO_TMP_DIR);
            case UPLOAD_ERR_CANT_WRITE:
                // Value: 7; Failed to write file to disk.
                throw new Exception('Не удалось записать файл на диск', UPLOAD_ERR_CANT_WRITE);
            case UPLOAD_ERR_EXTENSION:
                // Value: 8; A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.
                throw new Exception('Модуль PHP остановил загрузку файла.', UPLOAD_ERR_EXTENSION);
            default:
                throw new Exception('Неизвестная ошибка', 0);
        }
    }
    
    /**
     * Загружает файл по переданному пути (локальный файл, либо ссылка) и возвращает объект User.
     * @param string $fileLocalPathOrUrl
     * @param string $name
     * @param string $description
     * @return File
     * @throws Exception
     */
    public function upload(string $fileLocalPathOrUrl, string $name = '', string $description = ''): File
    {
        if ( empty($fileLocalPathOrUrl) ) {
            throw new InvalidArgumentException('Не задан путь до файла');
        }
        
        $name = $name ?: basename($fileLocalPathOrUrl);
        
        if ( $this->isUrl($fileLocalPathOrUrl) ) {
            // Загружаем файл по URL
            $tempFile = tempnam(sys_get_temp_dir(), 'tmp_');
            $result = file_put_contents($tempFile, fopen($fileLocalPathOrUrl, 'r'));
            if ( false === $result ) {
                throw new Exception('Ошибка при загрузке файла по URL: "' . $fileLocalPathOrUrl . '"');
            }
            $fileLocalPathOrUrl = $tempFile;
        } elseif ( !is_file($fileLocalPathOrUrl) ) {
            throw new Exception('Не является файлом: "' . $fileLocalPathOrUrl . '"');
        }
        
        $file = new File();
        $file->setOriginalName($name);
        $file->setTmpName($fileLocalPathOrUrl);
        $file->setFileName($name);
        $file->setDescription($description);
        
        $file = $this->persist($file);
        
        $file->setNewlyUploaded(true);
        
        return $file;
    }
    
    /**
     * @param string $path
     * @return bool
     */
    private function isUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
    
    /**
     * @return ?FileCollection
     * @throws Exception
     */
    public function find(): ?FileCollection
    {
        $files = $this->repository->find(
            $this->filter->get(),
            $this->sort->get(),
            $this->limit->getLimits()
        );
        $this->reset();
        return $files;
    }
    
    /**
     * @param int $id
     * @return File|null
     * @throws Exception
     */
    public function findById(int $id): ?File
    {
        $file = $this->repository->findById($id);
        $this->reset();
        return $file;
    }
    
    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
    
    /**
     * @return string
     */
    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }
    
    /**
     * @param string $uploadDir
     * @return FileManager
     * @throws Exception
     */
    public function setUploadDir(string $uploadDir): FileManager
    {
        if ( !is_dir($uploadDir) ) {
            $this->createDir($uploadDir);
        }
        
        if ( !is_writable($uploadDir) ) {
            if ( !chmod($uploadDir, 0777) ) { // @fixme magic number
                throw new Exception('Директория для загрузки файлов не доступна для записи');
            }
        }
        $this->uploadDir = $uploadDir;
        return $this;
    }
    
    /**
     * Рекурсивно создает директорию
     * @param string $uploadDir
     * @return $this
     * @throws Exception
     */
    private function createDir(string $uploadDir): self
    {
        if ( !mkdir($uploadDir, 0777, true) ) { // @fixme magic number
            throw new Exception('Не удалось создать директорию для загрузки файлов. Проверьте уровень доступа');
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->repository->getTotalCount();
    }
}