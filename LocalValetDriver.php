<?php

class LocalValetDriver extends BasicValetDriver
{
    /**
     * Concatenate the site path and URI as a single file name.
     *
     * @param  string  $sitePath
     * @param  string  $uri
     * @return string
     */
    protected function asActualFile($sitePath, $uri)
    {
        return $sitePath.DIRECTORY_SEPARATOR.'public'.$uri;
    }
}
