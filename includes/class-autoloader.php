<?php
if (!defined('ABSPATH')) {
    exit;
} // End if().

class Autoloader
{

    public function __construct()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($class)
    {

        $prefix     = 'Ksd\\';
        $len        = strlen($prefix);
        $classname  = substr($class, $len);
        $slash_pos  = strrpos($classname, '\\');
        $class_path = $classname;
        $class      = $classname;

        $base_dir = untrailingslashit(KSD_PLUGIN_DIR) . '/includes';

        if ($slash_pos) {
            $slash_pos   = strrpos($classname, '\\');
            $split_class = str_split($classname, $slash_pos);

            $class_path = $split_class[0];

            $class_path = '/' . $class_path;
            $class_path = str_replace('\\', '/', $class_path);
            $base_dir   = untrailingslashit(KSD_PLUGIN_DIR) . '/includes' . $class_path;

            $class     = explode('\\', $classname);
            $class_len = sizeof($class);
            $class     = $class[$class_len - 1];

        }

        $relative_class = strtolower($class);

        $wp_relative_class = str_replace('\\', '-', $relative_class);

        $wp_relative_class = str_replace('_', '-', $wp_relative_class);

        $wp_relative_class = '/class-' . $wp_relative_class;

        $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $wp_relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
}
return new Autoloader();
