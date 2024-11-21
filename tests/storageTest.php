<?php

include_once '../includes/autoload.php';
require '../vendor/autoload.php';

use LibMelanie\Storage\IStorage;
use LibMelanie\Storage\Storage;

// get the possible script parameters, it can only be a path to a file
$path = $argv[1];

// if the path is not set, we exit the script
if (!isset($path)) {
    echo "No path set\n";
    exit(1);
}

function storageTest($path, IStorage $storage)
{
    $trash = "";

    echo "S3 Storage test\n";
    echo "Write test\n";
    $storage->write('/uuuidhhdhdhdgssg/sfiouhjiobrpo/test.txt', 'test2');

    echo "Read test\n";
    echo $storage->read('/uuuidhhdhdhdgssg/sfiouhjiobrpo/test.txt') . "\n";
    fscanf(STDIN, "%s", $trash);

    echo "overwrite (update) test\n";
    $storage->write('/uuuidhhdhdhdgssg/sfiouhjiobrpo/test.txt', 'test modified');

    echo "Read test\n";
    echo $storage->read('/uuuidhhdhdhdgssg/sfiouhjiobrpo/test.txt') . "\n";
    fscanf(STDIN, "%s", $trash);

    
    echo "Delete test\n";
    $storage->delete('/uuuidhhdhdhdgssg/sfiouhjiobrpo/test.txt');
    
    echo "Read deleted file test\n";
    echo $storage->read('/uuuidhhdhdhdgssg/sfiouhjiobrpo/test.txt') . "\n";
    fscanf(STDIN, "%s", $trash);

    echo "Write test on " . $path . "\n";
    $storage->write($path, file_get_contents($path));
    fscanf(STDIN, "%s", $trash);

    echo "Delete test on " . $path . "\n";
    $storage->delete($path);
    
    echo "Read deleted file test on " . $path . "\n";
    echo $storage->read($path) . "\n";
    fscanf(STDIN, "%s", $trash);
}

function menu($path)
{
    do {
        echo "\033[2J";
        echo "1. Local Storage\n";
        echo "2. S3 Storage\n";
        echo "3. Swift Storage\n";
        echo "4. Postgresql Storage\n";
        echo "5. Exit\n";
        echo "Enter your choice: ";

        $choice = trim(fgets(STDIN));

        switch ($choice) {
            case 1:
                storageTest($path, Storage::getStorage(Storage::LOCAL));
                break;
            case 2:
                storageTest($path, Storage::getStorage(Storage::S3));
                break;
            case 3:
                storageTest($path, Storage::getStorage(Storage::SWIFT));
                break;
            // case 4:
            //     storageTest($path, Storage::getStorage(Storage::SQL));
            //     break;
            case 5:
                exit(0);
            default:
                echo "Invalid choice\n";
        }
    } while ($choice != 5);
}

menu($path);