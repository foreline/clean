<?php
    declare(strict_types=1);
    
    namespace Presentation\Helpers;
    
    use Domain\Event\Publisher;
    use Domain\Events\ExceptionOccurredEvent;
    use Exception;
    use JetBrains\PhpStorm\NoReturn;

    /**
     * Класс для работы с AJAX запросами. Выводит JSON
     * <pre>
     * $ajax = new Ajax();
     * if ( $error ) {
     *  $ajax->error('Ошибка');
     * } else {
     *  $ajax->response(['param'=>'value']);
     * }
     * </pre>
     * @deprecated
     */
    class Ajax
    {
        /** @var string Заголовок */
        private string $label = '';
        
        /** @var string Текст для кнопки */
        private string $submitText = 'OK';
        
        /** @var int $status Статус ответа */
        private int $status = 1;
        
        /** @var string $error Сообщение об ошибке */
        private string $error = '';
        
        /** @var string[] Массив с сообщениями об ошибке */
        private array $errors = [];
        
        /** @var string $errorCode Код ошибки */
        private string $errorCode = '';
        
        /** @var mixed Вывод */
        private mixed $result = '';
        
        /**
         * Конструктор класса
         */
        public function __construct()
        {
        }
        
        /**
         *
         */
        private function getResponseResult(): array
        {
            $data = [
                'status'    => $this->status,
                'error'     => $this->error,
                'errors'    => $this->errors,
                'errorCode' => $this->errorCode,
                //'label'     => $this->label,
                //'submitText'    => $this->submitText,
                'output'    => $this->result, // @deprecated
                'result'    => $this->result,
            ];
            
            // Собираем все магические свойства
            $properties = get_object_vars($this);
            
            foreach ( $properties as $key => $value ) {
                if ( !array_key_exists($key, $data) ) {
                    $data[$key] = $value;
                }
            }
            
            return $data;
        }
        
        /**
         * Выводит ошибку в виде JSON. Посылает HTTP заголовок.
         * @param string $errorMessage Сообщение об ошибке
         * @param int $errorCode Код ошибки
         */
        #[NoReturn]
        public function error(string $errorMessage, int $errorCode = 0): void
        {
            Publisher::getInstance()->publish(new ExceptionOccurredEvent(new Exception($errorMessage, $errorCode)));
            $this->status = 0;
            $this->error = $errorMessage;
            $this->errorCode = (string)$errorCode;
            $this->response();
        }
        
        /**
         * Выводит ошибку в виде JSON. Посылает HTTP заголовок. Создает доменное событие ExceptionOccurredEvent($e).
         * @param Exception $e
         */
        #[NoReturn]
        public function exception(Exception $e): void
        {
            Publisher::getInstance()->publish(new ExceptionOccurredEvent($e));
            $this->status = 0;
            $this->error = $e->getMessage();
            $this->errorCode = (string)$e->getCode();
            $this->response();
        }
        
        /**
         * Выводит содержимое в виде JSON добавляя поля status, error, errorCode.
         * Сами данные помещаются в ключ 'output'.
         * Посылает HTTP заголовок.
         * @param mixed $result
         */
        #[NoReturn]
        public function response(mixed $result = ''): void
        {
            if ( !empty($result) ) {
                $this->result = $result;
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($this->getResponseResult());
            exit();
        }
        
        /**
         * Выводит содержимое в формате JSON. Посылает HTTP заголовок
         * @param array $result
         */
        #[NoReturn]
        public function json(array $result = []): void
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit();
        }
        
        /**
         * @return string
         */
        public function getLabel(): string
        {
            return $this->label;
        }
        
        /**
         * @param string $label
         * @return Ajax
         */
        public function setLabel(string $label): Ajax
        {
            $this->label = $label;
            return $this;
        }
        
        /**
         * @return string
         */
        public function getSubmitText(): string
        {
            return $this->submitText;
        }
        
        /**
         * @param string $submitText
         * @return Ajax
         */
        public function setSubmitText(string $submitText): Ajax
        {
            $this->submitText = $submitText;
            return $this;
        }
        
        /**
         * @return int
         */
        public function getStatus(): int
        {
            return $this->status;
        }
        
        /**
         * @param int $status
         * @return Ajax
         */
        public function setStatus(int $status): Ajax
        {
            $this->status = $status;
            return $this;
        }
        
        /**
         * @return string
         */
        public function getError(): string
        {
            return $this->error;
        }
        
        /**
         * @param string $error
         * @return Ajax
         */
        public function setError(string $error): Ajax
        {
            $this->error = $error;
            return $this;
        }
        
        /**
         * @return array
         */
        public function getErrors(): array
        {
            return $this->errors;
        }
        
        /**
         * @param array|string $errors
         * @return Ajax
         */
        public function setErrors(array|string $errors): self
        {
            $this->errors = $errors;
            return $this;
        }
        
        /**
         * @param string $error
         * @return $this
         */
        public function addError(string $error): self
        {
            if ( !in_array($error, $this->errors, true)) {
                $this->errors[] = $error;
            }
            return $this;
        }
        
        /**
         * @return string
         */
        public function getErrorCode(): string
        {
            return $this->errorCode;
        }
        
        /**
         * @param string $errorCode
         * @return Ajax
         */
        public function setErrorCode(string $errorCode): Ajax
        {
            $this->errorCode = $errorCode;
            return $this;
        }
        
        /**
         * @param $name
         * @param $value
         * @return void
         */
        public function __set($name, $value): void
        {
            $this->$name = $value;
        }
        
        /**
         * @param $name
         * @return bool
         */
        public function __isset($name): bool
        {
            return isset($this->$name);
        }
        
        /**
         * @param $name
         * @return mixed
         */
        public function __get($name)
        {
            return $this->$name;
        }
    }