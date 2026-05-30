<?php
// network/cache-helper.php

function getCacheFile($key) {
    return sys_get_temp_dir() . '/noble_' . md5($key) . '.cache';
}
function getCache($key, $ttl = 60) {
    $file = getCacheFile($key);
    if (!file_exists($file)) return false;
    if ((time() - filemtime($file)) > $ttl) return false;
    return file_get_contents($file);
}
function setCache($key, $data, $ttl = 60) {
    file_put_contents(getCacheFile($key), $data, LOCK_EX);
}
function clearCache($key) {
    $file = getCacheFile($key);
    if (file_exists($file)) unlink($file);
}