<?php
    declare(strict_types=1);
    
    namespace Domain\File\UseCase;
    
    use Domain\File\Aggregate\File;
    use Domain\File\Aggregate\FileCollection;
    use Domain\File\Infrastructure\Bitrix\FileRepository; // @fixme
    use Domain\File\Infrastructure\Repository\FileRepositoryInterface;
    use Domain\UseCase\AbstractManager;
    use Exception;
    use InvalidArgumentException;
    
    /**
     * Service for working with File Aggregate. Singleton Pattern.
     * Uploading a file is a responsibility of FileRepository.
     */
    class FileManager extends AbstractManager
    {
        private FileRepositoryInterface $repository;
        
        private static ?self $instance = null;
        
        /** @var string Files upload Directory */
        private string $uploadDir = '';
        
        /**
         * @param FileRepositoryInterface|null $repository
         * @throws Exception
         * @return self
         */
        public static function getInstance(FileRepositoryInterface $repository = null): self
        {
            if ( !self::$instance ) {
                self::$instance = new self($repository);
            }
            return self::$instance;
        }
        
        /**
         * @param FileRepositoryInterface|null $repository
         * @throws Exception
         */
        private function __construct(FileRepositoryInterface $repository = null)
        {
            $this->repository = $repository ?? new FileRepository(); // @fixme use DI
            $this->setUploadDir($_SERVER['DOCUMENT_ROOT'] . '/upload/files'); // @fixme
            
            parent::__construct();
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
         * @param string $filePath
         * @param string $name
         * @param string $description
         * @return File
         * @throws Exception
         */
        public function upload(string $filePath, string $name = '', string $description = ''): File
        {
            if ( empty($filePath) ) {
                throw new InvalidArgumentException('Не задан файл');
            }
            
            if ( !is_file($filePath) ) {
                throw new Exception('Не является файлом');
            }
            
            $file = new File();
            $file->setOriginalName($name);
            $file->setTmpName($filePath);
            $file->setFileName($name);
            $file->setDescription($description);
            
            $file = $this->persist($file);
            
            $file->setNewlyUploaded(true);
            
            return $file;
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
        
    }