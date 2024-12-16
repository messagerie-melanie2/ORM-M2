#!/usr/bin/env php
<?php

include __DIR__ . '/Version.php';

if (isset($argv) && count($argv) == 2) {
    if ($argv[1] == 'version') {
        echo \LibMelanie\Version::VERSION;
        exit;
    }
    else if ($argv[1] == 'build') {
        echo \LibMelanie\Version::BUILD;
        exit;
    }
    else if ($argv[1] == 'all') {
        echo \LibMelanie\Version::VERSION . '_' . \LibMelanie\Version::BUILD;
        exit;
    }
}
echo \LibMelanie\Version::VERSION . '_' . \LibMelanie\Version::BUILD;
exit;
