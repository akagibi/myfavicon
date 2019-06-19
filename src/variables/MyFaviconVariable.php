<?php
/**
 * My Favicon plugin for Craft CMS 3.x
 *
 * CraftCMS plugin generating favicon.
 *
 * @link      https://www.akagibi.com
 * @copyright Copyright (c) 2019 Akagibi
 */

namespace akagibi\myfavicon\variables;

use akagibi\myfavicon\MyFavicon;
use Craft;

class MyFaviconVariable
{
    public function html()
    {
    	$html = file_get_contents(dirname(__DIR__, 1) . '/assets/favicon.html');

        echo(html_entity_decode($html));
    }
}
