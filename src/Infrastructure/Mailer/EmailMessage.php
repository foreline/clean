<?php
declare(strict_types=1);

namespace Infrastructure\Mailer;

/**
 * Класс почтового сообщения
 */
class EmailMessage implements MessageInterface
{
    /**
     * @var string
     */
    private string $to;
    /**
     * @var string
     */
    private string $from;
    /**
     * @var string
     */
    private string $subject;
    /**
     * @var string
     */
    private string $body;

    /**
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $body
     */
    public function __construct(string $to = '', string $from = '', string $subject = '', string $body = '')
    {
        $this->to = $to;
        $this->from = $from;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * @param string $to
     * @return MessageInterface
     */
    public function setTo(string $to): MessageInterface
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $from
     * @return MessageInterface
     */
    public function setFrom(string $from): MessageInterface
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $subject
     * @return MessageInterface
     */
    public function setSubject(string $subject): MessageInterface
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $body
     * @return MessageInterface
     */
    public function setBody(string $body): MessageInterface
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}