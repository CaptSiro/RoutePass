<?php

function dir_walker(string $dir, bool $files_only = true): Generator {
    $queue = [$dir];

    do {
        $current = realpath(array_shift($queue));
        $handle = opendir($current);

        while (false !== ($entry = readdir($handle))) {
            $path = realpath("$current/$entry");

            if ($entry === "." || $entry === "..") {
                continue;
            }

            if (is_dir($path)) {
                $queue[] = $path;

                if ($files_only === true) {
                    continue;
                }
            }

            yield $path;
        }

        closedir($handle);
    } while (!empty($queue));
}