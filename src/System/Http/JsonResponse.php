<?php

declare(strict_types=1);

namespace System\Http;

class JsonResponse extends Response
{
    protected string $data;

    /**
     * @see https://github.com/symfony/symfony/blob/6.4/src/Symfony/Component/HttpFoundation/JsonResponse.php
     */
    protected int $encoding_options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    /**
     * @param array<mixed, mixed>|null $data
     * @param array<string, string>    $headers Header tosend to client
     */
    public function __construct(?array $data = null, int $status_code = 200, array $headers = [])
    {
        parent::__construct('', $status_code, $headers);
        $data ??= [];
        $this->setData($data);
    }

    public function setEncodingOptions(int $encoding_options): self
    {
        $this->encoding_options = $encoding_options;
        $this->setData(json_decode($this->data));

        return $this;
    }

    public function getEncodingOptions(): int
    {
        return $this->encoding_options;
    }

    public function setJson(string $json): self
    {
        $this->data = $json;
        $this->prepare();

        return $this;
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public function setData(array $data): self
    {
        $this->data = json_encode($data, $this->encoding_options);
        $this->prepare();

        return $this;
    }

    /**
     * @return array<mixed, mixed>
     */
    public function getData(): array
    {
        return json_decode($this->data, true);
    }

    protected function prepare(): void
    {
        $this->setContentType('application/json');
        $this->setContent($this->data);
    }
}
