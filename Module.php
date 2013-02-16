<?php

namespace WaterMark;


class Module
{
    /**
     * Default autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * Create WaterMark service
     *
     * @return array
     */
    public function getServiceConfig()
    {
        $module = $this;

        return array(
            'factories' => array(
                'WaterMark' => function () use ($module) {
                    return $module;
                },
            ),
        );
    }

    /**
     * Create image thumbnail object
     *
     * @param  string $filename
     * @param  array $options
     * @param  bool $isDataStream
     * @return type
     */
    public function output($source, $watermarked_destination = null, $watermark_options = null)
    {
        try {
            $img = \WaterMark::output($source, $watermarked_destination, $watermark_options);
        } catch (Exception $e) {
            die($e->getMessage());
        }
        return $img;
    }
}
