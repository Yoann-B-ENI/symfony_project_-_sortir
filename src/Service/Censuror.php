<?php

namespace App\Service;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class Censuror
{
    private array $badwords;

    public function __construct(ParameterBagInterface $params)
    {
        $this->badwords = $params->get('censored_words');
    }

    private function normalizeLeetSpeak(string $text): string
    {
        $leetMap = [
            '0' => 'o', '1' => 'i', '2' => 'z', '3' => 'e', '4' => 'a',
            '5' => 's', '6' => 'g', '7' => 't', '8' => 'b', '9' => 'p',
            '@' => 'a', '$' => 's', '€' => 'e', '!' => 'i', '|' => 'i'
        ];

        return str_ireplace(array_keys($leetMap), array_values($leetMap), $text);
    }
    public function purify(string $text): string
    {

        $normalizedText = $this->normalizeLeetSpeak($text);

        foreach ($this->badwords as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            $replacement = str_repeat('*', mb_strlen($word));
            $normalizedText = preg_replace($pattern, $replacement, $normalizedText);
        }

        return $normalizedText;
    }




}