<?php

use RoutePass\tree\path\parser\Token;
use RoutePass\tree\path\parser\Tokenizer;
use RoutePass\tree\path\parser\TokenType;
use function sptf\functions\expect;
use function sptf\functions\test;

test("tokenize string /u/id-[id]", function () {
    $path = "/u/id-[id]";
    /** @var Token[] $found */
    $found = [...(new Tokenizer($path))->tokenize()];

    /** @var Token[] $final */
    $final = [
        new Token(TokenType::SLASH, "/"),
        new Token(TokenType::IDENT, "u"),
        new Token(TokenType::SLASH, "/"),
        new Token(TokenType::IDENT, "id-"),
        new Token(TokenType::BRACKET_L, "["),
        new Token(TokenType::IDENT, "id"),
        new Token(TokenType::BRACKET_R, "]"),
        new Token(TokenType::EOF, "\0"),
    ];
    $count = count($final);

    expect(count($found))->toBe($count);

    for ($i = 0; $i < count($final); $i++) {
        expect($found[$i]->type)->toBe($final[$i]->type);
        expect($found[$i]->literal)->toBe($final[$i]->literal);
    }
});