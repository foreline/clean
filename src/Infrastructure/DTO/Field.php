<?php
    declare(strict_types=1);
    
    namespace Infrastructure\DTO;
    
    /**
     * Поле объекта DTO
     * @deprecated
     */
    class Field
    {
        private string $code;
        private FieldType $type;
        private FormType $formType;
        
        public function __construct(string $code)
        {
            $this->code = $code;
        }
    
        /**
         * @return FieldType
         */
        public function getType(): FieldType
        {
            return $this->type;
        }
    
        /**
         * @param FieldType $type
         * @return Field
         */
        public function setType(FieldType $type): Field
        {
            $this->type = $type;
            return $this;
        }
    
        /**
         * @return FormType
         */
        public function getFormType(): FormType
        {
            return $this->formType;
        }
    
        /**
         * @param FormType $formType
         * @return Field
         */
        public function setFormType(FormType $formType): Field
        {
            $this->formType = $formType;
            return $this;
        }
        
    }