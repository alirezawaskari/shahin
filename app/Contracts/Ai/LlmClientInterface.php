<?php

namespace App\Contracts\Ai;

interface LlmClientInterface
{
    /**
     * Send a prompt to the LLM and return the generated text.
     *
     * @param string $prompt
     * @param array<string, mixed> $options E.g. ['system' => string, 'model' => string, 'temperature' => float]
     * @return string
     */
    public function generateText(string $prompt, array $options = []): string;

    /**
     * Send a prompt to the LLM and parse the generated JSON into an array.
     *
     * @param string $prompt
     * @param array<string, mixed> $options E.g. ['system' => string, 'model' => string, 'schema' => array]
     * @return array<string, mixed>
     */
    public function generateJson(string $prompt, array $options = []): array;
}
