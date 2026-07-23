<?php

namespace App\Http\Controllers;


class ImageManipulator extends Controller
{

    /**
     * @var string Location of current image
     */
    var $curfile = "";

    /**
     * @var string Filename of current image
     */
    var $filename = "";

    /**
     * @var string Extension of current image
     */
    var $ext = "";

    /**
     * @var mixed Resource holder for an image that is loaded into the object
     */
    var $im = null;

    /**
     * @var int Number between 0 and 100 representing output quality of image
     */
    var $quality = 90;

    /**
     * @var mixed RGB array representing the background colour used in fills
     */
    var $bgcolor = array(255,255,255);

    /**
     * @var mixed RGB array representing the foreground colour used in borders and text
     */
    var $color = array(200,200,200);

    /**
     * @var mixed Array of weights used when making an image grey scale.  Preferably add to 3 to keep same overall gamma.
     */
    var $gamma = array(1,1,1);	//Represent RGB values.



    /**
     * Constructor function - if optional filename is sent then it will auto load the imaeg into the object
     *
     * @param string $img image path or url
     * @return boolean Whether image was successfully loaded
     * @access public
     */
    public function ImageManipulator($img="") {

        if($img!="")
            return $this->load_image($img);
    }

    /**
     * Loads an image file into the class
     *
     * @param string $img image path or url
     * @return boolean Whether image was successfully loaded
     * @access public
     */
    public function load_image($img) {

        if(!file_exists($img) || !is_file($img)) return false;
        if (!getimagesize($img)) return false;
        $fileinfo = pathinfo($img);

        $this->filename = $fileinfo["basename"];
        $this->ext = strtolower($fileinfo["extension"]);

        $mime = mime_content_type($img);

        //depending on the image type create
        switch($mime) {
            case "image/gif": $im = imagecreatefromgif($img); break;
            case "image/jpeg": $im = imagecreatefromjpeg($img); break;
            case "image/png": $im = imagecreatefrompng($img); break;
            case "image/webp": $im = imagecreatefromwebp($img); break;
            default: $im = imagecreatefromjpeg($img); break;
        }
        if(!$im) return false;
        else $this->im = $im;

        return true;
    }

    /**
     * Destroys the image held in memory - always call this before the end of your script
     *
     * @access public
     */
    public function end() {
        if(!$this->im) return false;
        imagedestroy($this->im);
    }


    /**
     * Resamples the current image to a specified size.  This function does not take into accound aspect-ratio.
     *
     * @param integer $w Width of new image
     * @param integer $h Height of new image
     * @return boolean Whether resize was sucessful
     * @access public
     */
    public function resample($w,$h) {
        if(!$this->im) return false;

        //create a destination image
        $dest = imagecreatetruecolor($w,$h);

        //try and resample
        $r = imagecopyresampled($dest,$this->im,0,0,0,0,$w,$h,imagesx($this->im),imagesy($this->im));

        //resample was successful so replace original image with new one
        if($r) {
            imagedestroy($this->im);
            $this->im = imagecreatetruecolor($w,$h);
            imagecopy($this->im,$dest,0,0,0,0,$w,$h);
            imagedestroy($dest);
            return true;
        }

        //resample failed
        else return false;
    }

    /**
     * Uses resample to resize an image so that it fits inside a certain box while maintaining aspect
     * ratio.  If "clip "is true then it will resize to the smallest dimension and clip the remaining region.
     * If "clip" is false (default) then it will center the image inside the box, and paint a background
     * colour in the remaining area.
     *
     * @param integer $w Width of new image
     * @param integer $h Height of new image
     * @param boolean $noclip Whether to fill entire box or *clip* areas that overflow
     * @param boolean $noenlarge If true then the file won't be enlarged to fit the spaec but rather sit in the middle
     * @return boolean Whether it was sucessful
     * @access public
     */
    public function resize_to_fit($w,$h,$noclip=true,$noenlarge=false) {
        if(!$this->im) return false;

        $curw = imagesx($this->im);
        $curh = imagesy($this->im);

        //create a destination image
        $dest = imagecreatetruecolor($w,$h);

        //set background colour
        $bg = ImageColorAllocate($dest,$this->bgcolor[0],$this->bgcolor[1],$this->bgcolor[2]);
        ImageFilledRectangle ($dest, 0, 0, $w, $h, $bg);

        //if in clip mode then fit to shortest side, otherwise fit to longest
        if( ($noclip && $curw/$w>=$curh/$h) || (!$noclip && $curw/$w < $curh/$h) ) {
            $newh = $curh / ($curw/$w);
            $neww = $w;
        } else {
            $neww = $curw / ($curh/$h);
            $newh = $h;
        }

        //try and resample by resized region to middle of the image
        if($curw<$w && $curh<$h && $noenlarge) $r = imagecopyresampled($dest,$this->im, ($w/2)-($curw/2), ($h/2)-($curh/2), 0, 0, $curw, $curh, $curw, $curh); //no resize just copy into middle of canvas
        else $r = imagecopyresampled($dest,$this->im, ($w/2)-($neww/2), ($h/2)-($newh/2), 0, 0, $neww, $newh, $curw, $curh); //resize, up or down

        //finish up if it worked
        if($r) {
            imagedestroy($this->im);
            $this->im = imagecreatetruecolor($w,$h);
            imagecopy($this->im,$dest,0,0,0,0,$w,$h);
            imagedestroy($dest);
            return true;
        }
        //resample failed
        else return false;

    }

    /**
     * Sets a specific RGB colour as transparent (only works when outputing as a PNG or GIF)
     *
     * @param integer $r Red value
     * @param integer $g Green value
     * @param integer $b Blue value
     * @return boolean Whether it was sucessful
     * @access public
     */
    public function set_transparency($r=0,$g=0,$b=0) {
        if(!$this->im) return false;

        //find the index of the colour to make transparent, if it doesn't exist then exit.
        $index = imagecolorexact($this->im,$r,$g,$b);
        if($index==-1) return false;

        //set the transparency
        imagecolortransparent($this->im,$index);

        return true;
    }

    /**
     * Desaturates an image, id optional $shade variable = 0 then image will be grey scale, 1 = red, 2 = greem 3 = blue
     *
     * NOTE: By adjusting the weight array prior to desaturating the image you can split the channels of an image.
     *
     * @param integer $shade What colour to shade the image
     * @return boolean Whether it was sucessful
     * @access public
     */
    public function desaturate($shade=0){
        if(!$this->im) return false;

        //convert o,age to 256 colours
        ImageTrueColorToPalette($this->im,1,256);

        //calculate total number of colours in palette
        $total = ImageColorsTotal($this->im);

        //for each colour make it grey
        for($i=0;$i<$total;$i++){
            $old = ImageColorsForIndex($this->im,$i);

            //get correct colouring by performing a weighted average of the 3
            $grey = round(($this->gamma[0]*$old["red"] + $this->gamma[1]*$old["green"] + $this->gamma[2]*$old["blue"]) / 3);

            if($shade==1)      ImageColorSet($this->im,$i,$grey,0,0);
            else if($shade==2) ImageColorSet($this->im,$i,0,$grey,0);
            else if($shade==3) ImageColorSet($this->im,$i,0,0,$grey);
            else               ImageColorSet($this->im,$i,$grey,$grey,$grey);
        }

        return true;
    }

    /**
     * Converts an image to n-colours
     *
     * @param integer $colors Number of colours in pallete
     * @return boolean Whether it was sucessful
     * @access public
     */
    public function reduce_colors($colors=255) {
        //convert o,age to 256 colours
        if(!function_exists("ImageTrueColorToPalette")) return false;
        ImageTrueColorToPalette($this->im,1,$colors);
        return true;
    }

    /**
     * Draws a border round the edge of the image using the $color variable storred in the object
     *
     * @param integer $size Border size in pixels
     * @return boolean Whether it was sucessful
     * @access public
     */
    public function draw_border($size) {
        if(!$this->im) return false;

        //rectangle works out 1px bigger than you'd expect so...
        $size = $size-1;

        //get image size
        $w = imagesx($this->im);
        $h = imagesy($this->im);

        //get the border colour
        $c = imagecolorallocate($this->im,$this->color[0],$this->color[1],$this->color[2]);

        //draw lines
        ImageFilledRectangle($this->im,0,0,$w,$size,$c);	//top border
        ImageFilledRectangle($this->im,0,$h-$size-1,$w,$h,$c);	//bottom border
        ImageFilledRectangle($this->im,0,0,$size,$h,$c);		//left border
        ImageFilledRectangle($this->im,$w-$size-1,0,$w,$h,$c);	//right border

        return true;
    }

    /**
     * sends the image currently storred within this object as a jpeg to the output buffer (headers included)
     *
     * @return boolean Whether ouput was sucessful
     * @access public
     */
    public function output_jpeg() {
        if(!$this->im || !function_exists("imagejpeg")) return false;
        header('Content-Type: image/jpeg');
        header("Content-Disposition: inline; filename={$this->filename}.jpg");	//neat header to tell the browser what the filename should be
        imagejpeg($this->im,"",$this->quality);
        return true;
    }

    /**
     * sends the image currently storred within this object as a png to the output buffer (headers included)
     *
     * @return boolean Whether ouput was sucessful
     * @access public
     */
    public function output_png() {
        if(!$this->im || !function_exists("imagepng")) return false;
        header('Content-Type: image/jpeg');
        header("Content-Disposition: inline; filename={$this->filename}.png");
        imagepng($this->im);
        return true;
    }

    /**
     * Saves the image currently storred within this object as a jpeg on the local file system
     *
     * @return boolean Whether save was sucessful
     * @access public
     */
    public function save_jpeg($filename) {
        if(!$this->im || !function_exists("imagejpeg")) return false;
        imagejpeg($this->im,$filename,$this->quality);
        return true;
    }

    /**
     * Saves the image currently storred within this object as a png on the local file system
     *
     * @return boolean Whether sav was sucessful
     * @access public
     */
    public function save_png($filename) {
        if(!$this->im || !function_exists("imagepng")) return false;
        imagepng($this->im,$filename);
        return true;
    }

    /**
     * Saves the image currently storred within this object as a png on the local file system
     *
     * @return boolean Whether sav was sucessful
     * @access public
     */
    function save_webp($filename) {
        if(!$this->im || !function_exists("imagewebp")) return false;
        imagewebp($this->im,$filename,$this->quality);
        return true;
    }
}
