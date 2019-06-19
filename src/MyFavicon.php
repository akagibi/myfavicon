<?php
/**
 * My Favicon plugin for Craft CMS 3.x
 *
 * CraftCMS plugin generating favicon.
 *
 * @link      https://www.akagibi.com
 * @copyright Copyright (c) 2019 Akagibi
 */

namespace akagibi\myfavicon;

use yii\base\Event;

use Craft;
use craft\base\Plugin;
use craft\helpers\UrlHelper;
use craft\web\twig\variables\CraftVariable;

use akagibi\myfavicon\models\Settings;
use akagibi\myfavicon\variables\MyFaviconVariable;

class MyFavicon extends Plugin
{
	public static $plugin;
	public $hasCpSettings = true;

	public function init()
	{
		parent::init();
		self::$plugin = $this;

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			function (Event $event) {
				$variable = $event->sender;
				$variable->set('myfavicon', MyFaviconVariable::class);
			}
		);
	}

	protected function createSettingsModel()
	{
		return new Settings();
	}

	protected function settingsHtml(): string
	{
		$view = Craft::$app->getView();
		
		return $view->renderTemplate('myfavicon/settings', [
			'settings' => $this->getSettings(),
			'myAssetSelectConfig' => array(
				'id'                 => 'myAsset',
				'name'               => 'myAsset',
				'jsClass'            => 'Craft.AssetSelectInput',
				'elementType'        => 'craft\\elements\\Asset',
				'elements'           => $this->getSettings()['myAsset'] && count($this->getSettings()['myAsset']) ? [Craft::$app->getElements()->getElementById($this->getSettings()['myAsset'][0])] : null,
				//'sources'            => array('folder:1'),
				'criteria'           => array('kind' => array('image'), 'localeEnabled' => null),
				'limit'              => 1,
				'viewMode'           => 'table',
				'selectionLabel'     => Craft::t('app','Select image'),
			),
		]);
	}

	public function afterSaveSettings()
	{
		$settings = $this->getSettings();
		$siteName = str_replace('"', '', Craft::$app->getSites()->currentSite->name);

		if ($settings->myAsset) {
			$asset = \craft\elements\Asset::find()->id($settings->myAsset)->one();

			if ($asset) {
				$root = $_SERVER['DOCUMENT_ROOT'];
				$url = $asset->getUrl();
				$file = $root . $url;
				$dir = $root . UrlHelper::rootRelativeUrl(UrlHelper::siteUrl());
				
				$pathinfo = pathinfo($file);
				$ext = strtolower($pathinfo['extension']);
				
				// Generating images
				$this->createThumb($ext, $file, $dir, 36, 36, 'android-icon-36x36.png');
				$this->createThumb($ext, $file, $dir, 48, 48, 'android-icon-48x48.png');
				$this->createThumb($ext, $file, $dir, 72, 72, 'android-icon-72x72.png');
				$this->createThumb($ext, $file, $dir, 96, 96, 'android-icon-96x96.png');
				$this->createThumb($ext, $file, $dir, 144, 144, 'android-icon-144x144.png');
				$this->createThumb($ext, $file, $dir, 192, 192, 'android-icon-192x192.png');
				$this->createThumb($ext, $file, $dir, 57, 57, 'apple-icon-57x57.png');
				$this->createThumb($ext, $file, $dir, 60, 60, 'apple-icon-60x60.png');
				$this->createThumb($ext, $file, $dir, 72, 72, 'apple-icon-72x72.png');
				$this->createThumb($ext, $file, $dir, 76, 76, 'apple-icon-76x76.png');
				$this->createThumb($ext, $file, $dir, 114, 114, 'apple-icon-114x114.png');
				$this->createThumb($ext, $file, $dir, 120, 120, 'apple-icon-120x120.png');
				$this->createThumb($ext, $file, $dir, 144, 144, 'apple-icon-144x144.png');
				$this->createThumb($ext, $file, $dir, 152, 152, 'apple-icon-152x152.png');
				$this->createThumb($ext, $file, $dir, 180, 180, 'apple-icon-180x180.png');
				$this->createThumb($ext, $file, $dir, 192, 192, 'apple-icon-precomposed.png');
				$this->createThumb($ext, $file, $dir, 192, 192, 'apple-icon.png');
				$this->createThumb($ext, $file, $dir, 16, 16, 'favicon-16x16.png', 'favicon.ico');
				$this->createThumb($ext, $file, $dir, 32, 32, 'favicon-32x32.png');
				$this->createThumb($ext, $file, $dir, 96, 96, 'favicon-96x96.png');
				$this->createThumb($ext, $file, $dir, 70, 70, 'ms-icon-70x70.png');
				$this->createThumb($ext, $file, $dir, 144, 144, 'ms-icon-144x144.png');
				$this->createThumb($ext, $file, $dir, 150, 150, 'ms-icon-150x150.png');
				$this->createThumb($ext, $file, $dir, 310, 310, 'ms-icon-310x310.png');

				// Generating files
				$this->createFile($dir, str_replace('MY_APP_NAME', $siteName, file_get_contents(__DIR__ . '/assets/manifest.json')), 'manifest.json');
				$this->createFile($dir, file_get_contents(__DIR__ . '/assets/browserconfig.xml'), 'browserconfig.xml');
			}
		}
	}
	
	public function createFile($dest, $content, $filename)
	{
		$file = $dest . $filename;
		$handle = fopen($file, 'w');
		fwrite($handle, $content);
		fclose($handle);
	}

	public function createThumb($ext, $src, $dest, $targetWidth, $targetHeight, $filename, $filenameIco = false)
	{
		if ($ext == 'jpeg' || $ext == 'jpg') $image = imagecreatefromjpeg($src);
		else if ($ext == 'png') $image = imagecreatefrompng($src);
		else if ($ext == 'gif') $image = imagecreatefromgif($src);

		$width = imagesx($image);
		$height = imagesy($image);

		$thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

		if ($ext == 'gif' || $ext == 'png') {
			imagecolortransparent($thumbnail, imagecolorallocate($thumbnail, 0, 0, 0));

			if ($ext == 'png') {
				imagealphablending($thumbnail, false);
				imagesavealpha($thumbnail, true);
			}
		}

		imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
		imagepng($thumbnail, $dest . $filename, 0);

		if ($filenameIco)
			$this->createIco($thumbnail, $dest . $filenameIco);
	}

	public function createIco($image, $filename)
	{
		$x = imagesx($image);
		$y = imagesy($image);

		if ($filename)
			ob_start();

		ob_start();
		imagesavealpha($image, true);
		imagepng($image, null, 0);
		$png_data = ob_get_clean();
		echo pack('v3', 0, 1, 1);
		echo pack('C4v2V2', $x, $y, 0, 0, 1, 32, strlen($png_data), 22);
		echo $png_data;

		if ($filename)
			file_put_contents($filename, ob_get_clean());
	}
}