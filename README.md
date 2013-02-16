WaterMark
=========

Insert watermark on your images easily using this module to ZendFramework 2

## Setup

  Following steps are necessary to get this module working, considering a zf2-skeleton or very similar application:

  1. Run: `php composer.phar cleitonalmeida/watermark:dev-master`
  2. Add `WaterMark` to the enabled modules list.

## QuickStart

  - For example add following code to controller action, assume example image:

        //taking the image url
        $targetFolder = 'public/uploads/';
        $url = $renderer->basePath($targetFolder);
        $url = $_SERVER['DOCUMENT_ROOT'] . $url;

        $watermark = $this->getServiceLocator()->get('WaterMark');
        $watermark_options = array(
          'watermark' => $url . "/watermark.png",
          'halign' => +1,
          'valign' => +1,
          'hshift' => -10,
          'vshift' => -10,
          'type' => IMAGETYPE_JPEG,
          'jpeg-quality' => 70,
        );
        
        // Save watermarked image to file
        $watermark::output($url . "input_image.jpg", $url . "output_image.jpg", $watermark_options);
        
        
Please, if you are interested in this Zend Framework module report any issues and don't hesitate to contribute.

[Report a bug](https://github.com/CleitonAlmeida/watermark/issues) | [Fork me](https://github.com/CleitonAlmeida/watermark)
