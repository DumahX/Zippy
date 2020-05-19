<?php

/*
Basic zip implementation used within your CLI.

Usage:
First argument passed is the name of your new zip archive (you don't need to include the zip extension).
Second argument passed is the file or directory you're trying to zip.
*/

$zip_name = is_array( $argv ) && isset( $argv[1] ) ? $argv[1] : "zip";
$zip_file = is_array( $argv ) && isset( $argv[2] ) ? $argv[2] : false;

if( function_exists( 'exec' ) ) {

    if( $zip_file === false ) {
        // User did not provide the files/directories to zip, so we'll bail out.
        echo "You need to provide the files/directories to zip.\n";
    } else {
        @exec( "zip -r $zip_name.zip $zip_file 2>&1", $output, $return );
        if( is_array( $output ) ) {
            foreach( $output as $o ) {
                echo "Output: " . $o . "\n";
                sleep( 1 );
            }
        }

        if( $return === 127 ) {
            // 127 exit code returned which typically indicates that zip utilities can't be used.
            // Suggest enabling zip (this will only work with users who have root privileges to their server).
            echo "Zip utilies cannot be used. Try enabling zip on your server:\n";
            echo "Ubuntu/Debian: sudo apt install zip\n";
            echo "RHEL/CentOS: sudo yum install zip unzip\n";

            // Since exec is not available, fall back to ziparchive.
            echo "Falling back to ZipArchive...\n";

            if( $zip_file === false ) {
                echo "You need to provide the files/directories you'd like included in the zip.";
            } else {
                $zip = new ZipArchive();
                $zip->open( $zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE );

                $root_path = realpath( $zip_file );

                // Create recursive directory iterator.
                $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root_path ), RecursiveIteratorIterator::LEAVES_ONLY );
                foreach( $files as $name => $file ) {
                    // Skip directories as they are automatically included.
                    if( ! $file->isDir() ) {
                        // Get real and relative path for current file.
                        $file_path = $file->getRealPath();
                        $relative_path = substr( $file_path, strlen( $root_path ) + 1 );

                        // Add current file to zip archive.
                        $zip->addFile( $file_path, $relative_path );
                    }
                }

                echo "Zip created successfully under ZipArchive.\n Your zip's name is: $zip_name\n";

                $zip->close();
            }
        } else {
            // Zip appears to have been created.
            echo "Zip created successfully.\n Your zip's name is: $zip_name.zip\n";
        }
    }
} else {
    // Exec function does not exist, drop out and provide a list of disabled functions.
    echo "Exec function does not exist. Make sure it's enabled or not disabled in the php.ini file.";
    $disabled_functions = function_exists( 'ini_get' ) ? explode( ',', ini_get( 'disable_functions' ) ) : "ini_get() function does not exist.";
    
    if( is_array( $disabled_functions ) ) {
        foreach( $disabled_functions as $function ) {
            echo $function;
        }
    } else {
        // $disabled_functions is a string. This most likely means the ini_get function's absence was detected.
        echo $disabled_functions;
    }

    // Since exec is not available. Fall back to ziparchive.
    echo "Falling back to ZipArchive...\n";
    if( $zip_file === false ) {
        echo "You need to provide the files/directories you'd like included in the zip.";
    } else {
        $zip = new ZipArchive();
        $zip->open( $zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE );

        $root_path = realpath( $zip_file );

        // Create recursive directory iterator.
        $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root_path ), RecursiveIteratorIterator::LEAVES_ONLY );
        foreach( $files as $name => $file ) {
            // Skip directories as they are automatically included.
            if( ! $file->isDir() ) {
                // Get real and relative path for current file.
                $file_path = $file->getRealPath();
                $relative_path = substr( $file_path, strlen( $root_path ) + 1 );

                // Add current file to zip archive.
                $zip->addFile( $file_path, $relative_path );
            }
        }

        echo "Zip created successfully under ZipArchive.\n Your zip's name is: $zip_name\n";

        $zip->close();
    }
}

?>