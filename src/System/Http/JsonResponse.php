<?php

declare(strict_types=1);

namespace System\Http;

class JsonResponse extends Response
{
    protected int $encoding_option = 15;

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

    public function setEncodingOption(int $encoding_option): self
    {
        $this->encoding_option = $encoding_option;

        return $this;
    }

    public function setJson(string $json): self
    {
        $this->content = $json;
        $this->prepare();

        return $this;
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public function setData(array $data): self
    {
        $this->content = json_encode($data, $this->encoding_option);
        $this->prepare();

        return $this;
    }

    /**
     * @return array<mixed, mixed>
     */
    public function getData(): array
    {
        return json_decode($this->content, true);
    }

    protected function prepare(): void
    {
        $this->content_type = 'application/json';
    }
}
