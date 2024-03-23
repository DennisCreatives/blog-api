<?php

function uploadImage($imageData)
{
    // Check if the image data is valid
    if (!is_string($imageData) || empty($imageData)) {
        return false;
    }

    // Decode the image data
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

    // Generate a unique filename
    $filename = uniqid() . '.png';

    // Define the upload directory
    $uploadDir = 'uploads/';

    // Create the upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Save the image file
    $filepath = $uploadDir . $filename;
    if (file_put_contents($filepath, $imageData)) {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $filepath;
    } else {
        return false;
    }
}