<?php

class afConfigUtils {
    /**
     * Returns the path to the $module/config/$action.xml
     */
    public static function getPath($module, $action) {
        $context = sfContext::getInstance();
        $root = sfConfig::get("sf_root_dir");
        $application = $context->getConfiguration()->getApplication();
        $path = "$root/apps/$application/modules/$module/config/$action.xml";
        if(!file_exists($path)) {
            $path = "$root/plugins/appFlowerPlugin/modules/$module/config/$action.xml";
        }
        return $path;
    }

    /**
     * Returns the XML config DOM document.
     */
    public static function getDoc($module, $action) {
        $path = self::getPath($module, $action);
        $doc = new DOMDocument();
        $doc->load($path);
        return $doc;
    }
}