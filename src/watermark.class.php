<?php

/**
 * This is a driver for the watermarks creating
 *
 * LICENSE:
 * The PHP License, version 3.0
 *
 * Copyright (c) 1997-2005 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following url:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * Class based on http://code.google.com/p/php-funs/
 *
 * @license     http://www.php.net/license/3_0.txt
 *              The PHP License, version 3.0
 */
class Watermark {
    /**
     * Horizontal align constants
     */

    const ALIGN_LEFT = -1;
    const ALIGN_CENTER = 0;
    const ALIGN_RIGHT = +1;

    /**
     * Vertical align constants
     */
    const ALIGN_TOP = -1;
    const ALIGN_MIDDLE = 0;
    const ALIGN_BOTTOM = +1;
   

    /**
     * Display rendered image (send it to browser or to file).
     * This method is a common implementation to render and output an image.
     * The method calls the render() method automatically and outputs the
     * image to the browser or to the file.
     *
     * @param  mixed   $input   Destination image, a filename or an image string data or a GD image resource
     * @param  array   $options Watermark options
     *         <pre>
     *         watermark	string	Watermark image filename
     *         halign		int		Horizontal align; one of Watermark::ALIGN_* constants
     *         valign		int		Vertical align; one of Watermark::ALIGN_* constants
     *         hshift		int		Horizontal shift
     *         vshift		int		Vertical shift
     *         type			int		One of IMAGETYPE_* constants supported by class
     *         jpeg-quality	int		JPEG quality level
     *         </pre>
     *
     * @return boolean          TRUE on success or FALSE on failure.
     * @access public
     */
    public static function output($input, $output = null, $options = null) {
        // Set default options
        static $defOptions = array(
    'watermark' => '',
    'halign' => self::ALIGN_CENTER,
    'valign' => self::ALIGN_MIDDLE,
    'hshift' => 0,
    'vshift' => 0,
    'type' => IMAGETYPE_JPEG,
    'jpeg-quality' => 90,
        );

        foreach ($defOptions as $k => $v) {
            if (!isset($options[$k])) {
                $options[$k] = $v;
            }
        }

        // Load source file and render image
        $renderImage = self::_render($input, $options);
        if (!$renderImage) {
            //die('Error rendering image'.E_USER_NOTICE);
            user_error('Error rendering image', E_USER_NOTICE);
            return false;
        }

        // Before output to browsers send appropriate headers
        if (empty($output)) {
            $content_type = image_type_to_mime_type($options['type']);
            if (!headers_sent()) {
                header('Content-Type: ' . $content_type);
            } else {
                user_error('Headers have already been sent. Could not display image.', E_USER_NOTICE);
                return false;
            }
        }

        // Define outputing function
        switch ($options['type']) {
            case IMAGETYPE_GIF:
                $result = empty($output) ? imagegif($renderImage) : imagegif($renderImage, $output);
                break;

            case IMAGETYPE_PNG:
                $result = empty($output) ? imagepng($renderImage) : imagepng($renderImage, $output);
                break;

            case IMAGETYPE_JPEG:
                $result = empty($output) ? imagejpeg($renderImage, '', $options['jpeg-quality']) : imagejpeg($renderImage, $output, $options['jpeg-quality']);
                break;

            default:
                user_error('Image type ' . $content_type . ' not supported by PHP', E_USER_NOTICE);
                return false;
        }

        // Output image (to browser or to file)
        if (!$result) {
            user_error('Error output image', E_USER_NOTICE);
            return false;
        }

        // Free a memory from the target image
        imagedestroy($renderImage);

        return true;
    }

    /**
     * Draw watermark to resource.
     *
     * @param  mixed   $input   Destination image, a filename or an image string data or a GD image resource
     * @param  array   $options Watermark options
     *
     * @return boolean			TRUE on success or FALSE on failure.
     * @access public
     * @see    					Watermark::output()
     */
    private static function _render($input, $options) {
        $sourceImage = self::_imageCreate($input);
        if (!is_resource($sourceImage)) {
            user_error('Invalid image resource', E_USER_NOTICE);
            return false;
        }

        $watermark = self::_imageCreate($options['watermark']);
        if (!is_resource($watermark)) {
            user_error('Invalid watermark resource', E_USER_NOTICE);
            return false;
        }

        $image_width = imagesx($sourceImage);
        $image_height = imagesy($sourceImage);
        $watermark_width = imagesx($watermark);
        $watermark_height = imagesy($watermark);
        $X = self::_coord($options['halign'], $image_width, $watermark_width) + $options['hshift'];
        $Y = self::_coord($options['valign'], $image_height, $watermark_height) + $options['vshift'];

        imagecopy($sourceImage, $watermark, $X, $Y, 0, 0, $watermark_width, $watermark_height);
        imagedestroy($watermark);

        return $sourceImage;
    }

    /**
     * Create a GD image resource from given input.
     *
     * This method tried to detect what the input, if it is a file the
     * createImageFromFile will be called, otherwise createImageFromString().
     *
     * @param  mixed $input The input for creating an image resource. The value
     *                      may a string of filename, string of image data or
     *                      GD image resource.
     *
     * @return resource     An GD image resource on success or false
     * @access public
     * @static
     * @see    Watermark::imageCreateFromFile(), Watermark::imageCreateFromString()
     */
    private static function _imageCreate($input) {
        if (is_file($input)) {
            return self::_imageCreateFromFile($input);
        } else if (is_string($input)) {
            return self::_imageCreateFromString($input);
        } else {
            return $input;
        }
    }

    /**
     * Create a GD image resource from file (JPEG, PNG support).
     *
     * @param  string $filename The image filename.
     *
     * @return mixed            GD image resource on success, FALSE on failure.
     * @access private
     * @static
     */
    private static function _imageCreateFromFile($filename) {
        if (!is_file($filename) || !is_readable($filename)) {
            user_error('Unable to open file "' . $filename . '"', E_USER_NOTICE);
            return false;
        }

        // determine image format
        list(,, $type) = getimagesize($filename);

        switch ($type) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
                break;

            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filename);
                break;

            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
                break;
        }
        user_error('Unsupport image type', E_USER_NOTICE);
        return false;
    }

    /**
     * Create a GD image resource from a string data.
     *
     * @param  string $string The string image data.
     *
     * @return mixed          GD image resource on success, FALSE on failure.
     * @access private
     * @static
     */
    private static function _imageCreateFromString($string) {
        if (!is_string($string) || empty($string)) {
            user_error('Invalid image value in string', E_USER_NOTICE);
            return false;
        }

        return imagecreatefromstring($string);
    }

    /**
     * Calculate watermark X or Y coordinate based on align and dimensions
     *
     * @param  int $align				One of Watermark::ALIGN_* constants
     * @param  int $image_dimension		The string image data.
     * @param  int $watermark_dimension	The string image data.
     *
     * @return int			Coordinate
     * @access private
     * @static
     */
    private static function _coord($align, $image_dimension, $watermark_dimension) {
        if ($align < self::ALIGN_CENTER) {
            $result = 0;
        } elseif ($align > self::ALIGN_CENTER) {
            $result = $image_dimension - $watermark_dimension;
        } else {
            $result = ($image_dimension - $watermark_dimension) >> 1;
        }
        return $result;
    }

}