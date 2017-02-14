<?php

/*
 *  Image Class - for image handling
 *
 *  Programmed and maintained by: Lee, Chung-ching <ahbolee@infotoo.com>
 *  Copyright (c) 2011-2014 Infotoo International Limited - http://www.infotoo.com
 *
 *  Version: 2.0.1
 *  Last Updated: 2014-08-19
 */

class Image
{
    const JPEG = IMAGETYPE_JPEG;
    const PNG = IMAGETYPE_PNG;
    const GIF = IMAGETYPE_GIF;

    public $img = null;

    public $width = 0;
    public $height = 0;
    public $truecolor = true;
    public $bgcolor = null;

    public $file_path = '';

    /* inherited setting */
    public $setting_default_bgcolor = 0;        //auto-set with fillColor()
    public $setting_default_color = 0;
    public $setting_jpeg_quality = 100;            // JPEG quality: ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
    public $setting_png_quality = 0;            // PNG quality: compression level: from 0 (no compression) to 9.
    public $type = null;                        // Image::JPEG, Image::PNG, Image::GIF

    function __construct($file_path = null, $x = null, $y = null, $truecolor = true, $bgcolor = null)
    {
        if ($file_path != null) {
            $this->load($file_path);
        } elseif ($x != null and $y != null) {
            $this->create($x, $y, $truecolor, $bgcolor);
        }
    }

    function load($file_path)
    {
        $this->file_path = $file_path;
        if (!file_exists($this->file_path)) {
            return false;
        }

        $info = getimagesize($this->file_path);
        switch ($info[2]) {
            case Image::JPEG:
                $this->img = @imagecreatefromjpeg($this->file_path);
                break;
            case Image::PNG:
                $this->img = @imagecreatefrompng($this->file_path);
                break;
            case Image::GIF:
                $this->img = @imagecreatefromgif($this->file_path);
                break;
            default:
                return false;
        }
        $this->saveAlpha();

        $this->width = $info[0];
        $this->height = $info[1];
        $this->type = $info[2];

        if ($this->type == Image::JPEG and function_exists('exif_read_data')) {
            $exif = @exif_read_data($this->file_path);
            if ($exif !== false and isset($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 6: //rotate 90
                        $fixed_img = $this->rotate('right');
                        $this->img = $fixed_img->img;
                        $this->width = $fixed_img->width;
                        $this->height = $fixed_img->height;
                        $fixed_img = null;
                        break;
                    case 3: //rotate 180
                        $fixed_img = $this->mirror('vertical');
                        $this->img = $fixed_img->img;
                        $this->width = $fixed_img->width;
                        $this->height = $fixed_img->height;
                        $fixed_img = null;
                        break;
                    case 8: //rotate 270
                        $fixed_img = $this->rotate('left');
                        $this->img = $fixed_img->img;
                        $this->width = $fixed_img->width;
                        $this->height = $fixed_img->height;
                        $fixed_img = null;
                        break;
                }
            }
        }
        return true;
    }

    function saveAlpha($mode = true)
    {
        if ($mode == true) {
            imagealphablending($this->img, false);
            imagesavealpha($this->img, true);
        } else {
            imagealphablending($this->img, true);
            imagesavealpha($this->img, false);
        }
    }

    /* create, import and export */

    function rotate($direction = 'left')
    {
        $output = $this->newImage();
        switch ($direction) {
            case 'left':
                for ($x = 0; $x < $this->width; $x++) {
                    for ($y = 0; $y < $this->height; $y++) {
                        imagesetpixel($output->img, $y, $this->width - $x, imagecolorat($this->img, $x, $y));
                    }
                }
                return $output;
            case 'right':
                for ($x = 0; $x < $this->width; $x++) {
                    for ($y = 0; $y < $this->height; $y++) {
                        imagesetpixel($output->img, $this->height - $y, $x, imagecolorat($this->img, $x, $y));
                    }
                }
                return $output;
        }
        return false;
    }

    function newImage($width = null, $height = null, $truecolor = null, $bgcolor = null)
    {
        if (is_null($width)) {
            $width = $this->width;
        }
        if (is_null($height)) {
            $height = $this->height;
        }
        if (is_null($truecolor)) {
            $truecolor = $this->truecolor;
        }
        if (is_null($bgcolor)) {
            $bgcolor = $this->bgcolor;
        }

        $img = new Image(null, $width, $height, $truecolor, $bgcolor);
        $img->setting_default_bgcolor = $this->setting_default_bgcolor;
        $img->setting_default_color = $this->setting_default_color;
        $img->setting_jpeg_quality = $this->setting_jpeg_quality;
        $img->setting_png_quality = $this->setting_png_quality;
        $img->type = $this->type;
        return $img;
    }

    function mirror($direction = 'horizontal')
    {
        $output = $this->newImage();
        switch ($direction) {
            case 'horizontal':
                for ($x = 0; $x < $this->width; $x++) {
                    for ($y = 0; $y < $this->height; $y++) {
                        imagesetpixel($output->img, $this->width - $x, $y, imagecolorat($this->img, $x, $y));
                    }
                }
                return $output;
            case 'vertical':
                for ($x = 0; $x < $this->width; $x++) {
                    for ($y = 0; $y < $this->height; $y++) {
                        imagesetpixel($output->img, $x, $this->height - $y, imagecolorat($this->img, $x, $y));
                    }
                }
                return $output;
        }
        return false;
    }

    function create($width, $height, $truecolor = true, $bgcolor = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->truecolor = $truecolor;
        $this->bgcolor = $bgcolor;
        $this->type = Image::PNG;

        if ($this->truecolor) {
            $this->img = imagecreatetruecolor($this->width, $this->height);
        } else {
            $this->img = imagecreate($this->width, $this->height);
        }
        $this->saveAlpha();
        if (!is_array($this->bgcolor)) {
            $this->fillColor(0, 0, 0, 127); //black & transparent
        } elseif (isset($this->bgcolor['r']) and isset($this->bgcolor['g']) and isset($this->bgcolor['b'])) {
            $this->fillColor($this->bgcolor['r'], $this->bgcolor['g'], $this->bgcolor['b'], isset($this->bgcolor['a']) ? $this->bgcolor['a'] : 0);
        } else {
            $this->fillColor(@$this->bgcolor[0], @$this->bgcolor[1], @$this->bgcolor[2], isset($this->bgcolor[3]) ? $this->bgcolor[3] : 0);
        }

    }

    function fillColor($red, $green, $blue, $alpha = 0)
    {
        $this->setDefaultBgcolor($red, $green, $blue, $alpha);
        imagefill($this->img, 0, 0, $this->setting_default_bgcolor);
    }

    function setDefaultBgcolor($red, $green, $blue, $alpha = 0)
    {
        $this->setting_default_bgcolor = imagecolorallocatealpha($this->img, $red, $green, $blue, $alpha);
    }

    function isImage()
    {
        return ($this->type != null);
    }

    /* color */

    function importSetting($img)
    {
        $this->setting_default_bgcolor = $img->setting_default_bgcolor;
        $this->setting_default_color = $img->setting_default_color;
        $this->setting_jpeg_quality = $img->setting_jpeg_quality;
        $this->setting_png_quality = $img->setting_png_quality;
        $this->type = $img->type;
    }

    function copyFrom($img)
    {
        $this->img = $img;
        $this->saveAlpha();
        $this->width = imagesx($this->img);
        $this->height = imagesy($this->img);
        $this->type = Image::PNG;
    }

    function save($file_path = null, $type = null)
    {
        if ($file_path == null) {
            $file_path = $this->file_path;
        }
        if ($type == null) {
            $type = $this->type;
        }
        switch ($type) {
            case Image::JPEG:
                //hide warning message
                return @imagejpeg($this->img, $file_path, $this->setting_jpeg_quality);
                break;
            case Image::PNG:
                $this->saveAlpha();
                //hide warning message
                return @imagepng($this->img, $file_path, $this->setting_png_quality);
                break;
            case Image::GIF:
                //hide warning message
                $this->saveAlpha();
                return @imagegif($this->img, $file_path);
                break;
            default:
                return false;
        }
    }

    function export($type = null)
    {
        if ($type == null) {
            $type = $this->type;
        }
        switch ($type) {
            case Image::JPEG:
                header('Content-Type: image/jpeg');
                return imagejpeg($this->img, null, $this->setting_jpeg_quality);
                break;
            case Image::PNG:
                header('Content-Type: image/png');
                $this->saveAlpha();
                return imagepng($this->img, null, $this->setting_png_quality);
                break;
            case Image::GIF:
                header('Content-Type: image/gif');
                $this->saveAlpha();
                return imagegif($this->img, null);
                break;
            default:
                return;
        }
    }

    function getColor($x, $y)
    {
        return imagecolorsforindex($this->img, imagecolorat($this->img, $x, $y));
    }

    function setColor($x, $y, $red, $green, $blue, $alpha = 0)
    {
        imagesetpixel($this->img, $x, $y, imagecolorallocatealpha($this->img, $red, $green, $blue, $alpha));
    }

    /* resize, crop */

    function setDefaultColor($red, $green, $blue, $alpha = 0)
    {
        $this->setting_default_color = imagecolorallocatealpha($this->img, $red, $green, $blue, $alpha);
    }

    function resize($new_width, $new_height = null) //faster but less quality
    {
        if ($new_height == null) {
            $new_height = round($new_width * $this->height / $this->width);
        } elseif ($new_width == null) {
            $new_width = round($new_height * $this->width / $this->height);
        }
        //hide warning message
        @imagecopyresized($output->img, $this->img, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return $output;
    }

    function convert_256color()
    {
        @imagetruecolortopalette($this->img, false, 255);
        $this->truecolor = false;
    }

    /* mirror */

    function detectFace()
    {
        $folder = dirname(__FILE__) . '/FaceDetector/';
        $result = false;
        if (file_exists($folder . 'FaceDetector.mauricesvay.php')) {
            require_once($folder . 'FaceDetector.mauricesvay.php');
            $detector = new FaceDetector($folder . 'detection.dat');
            if ($detector->faceDetect($this->resample($this->width / 10)->img)) {
                $r = $detector->getFace();
                $result = array(
                    'x' => $r['x'] * 10,
                    'y' => $r['y'] * 10,
                    'w' => $r['w'] * 10,
                    'h' => $r['w'] * 10,
                );
            }
        }
        return $result;
    }

    /* rotate */

    function resample($new_width, $new_height = null)
    {
        if ($new_height == null) {
            $new_height = round($new_width * $this->height / $this->width);
        } elseif ($new_width == null) {
            $new_width = round($new_height * $this->width / $this->height);
        }
        $output = $this->newImage($new_width, $new_height);
        //hide warning message
        @imagecopyresampled($output->img, $this->img, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
        return $output;
    }


    /* copy image */

    function thumb($width, $height, $bgcolor = null, $focus_location = false)
    {
        if ($width == null) {
            if ($this->height > $height) {
                $img = $this->resample(null, $height);
            } else {
                $img = $this->cloneImage();
            }
            $width = $img->width;
        } elseif ($height == null) {
            if ($this->width > $width) {
                $img = $this->resample($width, null);
            } else {
                $img = $this->cloneImage();
            }
            $height = $img->height;
        } elseif ($width == $height and $this->width == $this->height) {
            if ($this->width > $width) {
                $img = $this->resample($width, $height);
            } else {
                $img = $this->cloneImage();
            }
        } else {
            $loc = $this->thumbLocation($width, $height, $focus_location);
            $img = $this->crop($loc['x'], $loc['y'], $loc['w'], $loc['h']);
            if ($loc['ratio'] != 1) {
                $img = $img->resample($loc['w'] / $loc['ratio'], $loc['h'] / $loc['ratio']);
            }
        }

        $output = $this->newImage($width, $height, null, $bgcolor);
        $x = max(($width - $img->width) / 2, 0);
        $y = max(($height - $img->height) / 2, 0);
        return $output->copyImage($x, $y, $img);
    }

    /* convert color */

    function cloneImage()
    {
        $output = $this->newImage();
        @imagecopy($output->img, $this->img, 0, 0, 0, 0, $this->width, $this->height);
        $output->saveAlpha();
        $output->type = $this->type;
        return $output;
    }

    /* face detection */

    function thumbLocation($width, $height, $focus_location = false)
    {
        $src = array('w' => $this->width, 'h' => $this->height);
        $exp = array('w' => $width, 'h' => $height);
        $loc = is_array($focus_location) ? $focus_location : $this->detechFocus();

        //force extend expected area larger then locate area
        $loc['min'] = min($loc['w'], $loc['h']);
        $ratio = 1;
        if ($exp['w'] < $loc['min'] or $exp['h'] < $loc['min']) {
            $ratio = max($loc['w'] / $exp['w'], $loc['h'] / $exp['h']);
            $exp['w'] = $exp['w'] * $ratio;
            $exp['h'] = $exp['h'] * $ratio;
        }

        //limit expected area not larger then source
        if ($exp['w'] > $src['w']) {
            $exp['w'] = $src['w'];
        }
        if ($exp['h'] > $src['h']) {
            $exp['h'] = $src['h'];
        }

        //extend locate area to expected area
        $dw = $exp['w'] - $loc['min'];
        $dh = $exp['h'] - $loc['min'];
        $loc['w'] = $loc['w'] + $dw;
        $loc['h'] = $loc['h'] + $dh;
        $loc['x'] = min(max($loc['x'] - $dw / 2, 0), $src['w'] - $loc['w']);
        $loc['y'] = min(max($loc['y'] - $dh / 2, 0), $src['h'] - $loc['h']);

        //extend to remains area
        $dw = $src['w'] - $loc['w']; //min margin width
        $dh = $src['h'] - $loc['h']; //min margin height
        if ($dw > 0 and $dh > 0) {
            $r = $exp['w'] / $exp['h'];
            if ($dw / $dh > $r) {
                $dw = floor($dh * $r);
                $ratio = $ratio * ($loc['w'] + $dw) / $loc['w'];
            } else {
                $dh = floor($dw / $r);
                $ratio = $ratio * ($loc['h'] + $dh) / $loc['h'];
            }
            $loc['w'] = $loc['w'] + $dw;
            $loc['h'] = $loc['h'] + $dh;
            $loc['x'] = min(max($loc['x'] - $dw / 2, 0), $src['w'] - $loc['w']);
            $loc['y'] = min(max($loc['y'] - $dh / 2, 0), $src['h'] - $loc['h']);
        }

        return array(
            'x' => $loc['x'],
            'y' => $loc['y'],
            'w' => $loc['w'],
            'h' => $loc['h'],
            'ratio' => $ratio,
        );
    }

    function detechFocus()
    {
        $side = min($this->width / 4, $this->height / 4);
        return array(
            'x' => max(floor(($this->width - $side) / 2), 0),
            'y' => max(floor(($this->height * 0.5 - $side) / 2), 0),
            'w' => $side,
            'h' => $side,
        );
    }

    /* thumbnail */

    function crop($x, $y, $new_width, $new_height)
    {
        $output = $this->newImage($new_width, $new_height);
        //hide warning message
        @imagecopyresized($output->img, $this->img, 0, 0, $x, $y, $new_width, $new_height, $new_width, $new_height);
        return $output;
    }

    function copyImage($x, $y, $source_image)
    {
        $output = $this->cloneImage();
        $output->saveAlpha(false);
        @imagecopy($output->img, $source_image->img, $x, $y, 0, 0, $source_image->width, $source_image->height);
        return $output;
    }
}
