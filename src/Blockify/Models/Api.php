<?php
namespace Blockify\Models;

class Api
{
    private bool $status;
    private string $message;
    private array $result;

    public function __construct(bool $status = false, string $message = "", array $result = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->result = !empty($result) ? $result : [];
    }

    public function toString(): string
    {
        return json_encode
        (
            array(
                "status" => self::getStatus() ? "ok" : "no",
                "message" => self::getMessage(),
                "result" => self::getResult()),
            JSON_PRETTY_PRINT
        );
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
