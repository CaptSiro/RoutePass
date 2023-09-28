<?php



use RoutePass\tree\path\parser\Parser;
use RoutePass\tree\path\parser\Token;
use RoutePass\tree\path\parser\Tokenizer;
use RoutePass\tree\path\parser\TokenType;
use RoutePass\tree\path\PartType;
use RoutePass\tree\path\Path;
use function sptf\functions\expect;
use function sptf\functions\pass;
use function sptf\functions\test;
use function sptf\functions\fail;



test("tokenize path /u/id-[id]", function () {
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



test("parse self-referencing paths", function () {
    $paths = ["", "/"];

    $final = Path::fromRaw([]);
    $compare = fn(Path $a, Path $b) => Path::compare($a, $b);

    foreach ($paths as $path) {
        try {
            $p = Parser::parse($path);

            expect($p)->toBe($final)->compare($compare);
        } catch (Exception) {
            fail("Should have parsed path: '$path'");
            continue;
        }
    }
});



test("parse paths", function () {
    $path = "/u/id-[id][user_name]/";
    $final = Path::fromRaw([
        [
            [PartType::STATIC, "u"]
        ],
        [
            [PartType::STATIC, "id-"],
            [PartType::DYNAMIC, "id"],
            [PartType::DYNAMIC, "user_name"],
        ]
    ]);

    try {
        $p = Parser::parse($path);
    } catch (Exception $e) {
        fail("Path is valid: ". $e->getMessage());
        return;
    }

    expect($p)->toBe($final)
        ->compare(fn(Path $a, Path $b) => Path::compare($a, $b));
});



test("fail parsing", function () {
    $paths = ["/u//", "//", "///", "[a", "b]", "[a[b]]", "[/]"];

    foreach ($paths as $path) {
        try {
            Parser::parse($path);
        } catch (Exception) {
            pass();
            continue;
        }

        fail("Should have failed parsing path: '$path'");
    }
});



test("bind parameters", function () {
    $pass = true;
    $params = [
        "a" => "/\d/",
        "b" => "/[bca]+/",
        "c" => "/[0-9]+/",
    ];

    try {
        $path = Parser::parse("/[a]/b-[b]/c-[c]-c");

        foreach ($params as $name => $value) {
            $path->param($name, $value);
        }

        foreach ($path->getSegments() as $segment) {
            foreach ($segment->getParts() as $part) {
                foreach ($params as $name => $value) {
                    if ($part->type === PartType::DYNAMIC
                        && $part->literal === $name
                        && $part->pattern !== $value) {
                        fail("Parameter of name '$name' must have pattern $value");
                        $pass = false;
                    }
                }
            }
        }
    } catch (Exception $e) {
        fail($e->getMessage());
        $pass = false;
    }

    if ($pass) {
        pass();
    }
});