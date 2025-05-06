<?php
declare(strict_types=1);

namespace Developion\Toolbox\Models;

use Developion\Toolbox\Helpers\Colors;

class Color
{
	private $_hex;

	private $_hsl;

	private $_rgb;

	public const DEFAULT_ADJUST = 10;

	public function __construct(string $hex)
	{
		$color = Colors::sanitizeHex($hex);
		$this->_hex = $color;
		$this->_hsl = Colors::hexToHsl($color);
		$this->_rgb = Colors::hexToRgb($color);
	}

	public function darken(int $amount = self::DEFAULT_ADJUST): string
	{
		$darkerHSL = $this->darkenHsl($this->_hsl, $amount);
		return Colors::hslToHex($darkerHSL);
	}

	public function lighten(int $amount = self::DEFAULT_ADJUST): string
	{
		$lighterHSL = $this->lightenHsl($this->_hsl, $amount);
		return Colors::hslToHex($lighterHSL);
	}

	public function mix(string $hex2, int $amount = 0): string
	{
		$rgb2 = Colors::hexToRgb($hex2);
		$mixed = $this->mixRgb($this->_rgb, $rgb2, $amount);
		return Colors::rgbToHex($mixed);
	}

	public function makeGradient(int $amount = self::DEFAULT_ADJUST): array
	{
		if ($this->isLight()) {
			$lightColor = $this->_hex;
			$darkColor = $this->darken($amount);
		} else {
			$lightColor = $this->lighten($amount);
			$darkColor = $this->_hex;
		}

		return array("light" => $lightColor, "dark" => $darkColor);
	}

	public function isLight($color = false, int $lighterThan = 130): bool
	{
		$color = ($color) ? $color : $this->_hex;

		$r = hexdec($color[0] . $color[1]);
		$g = hexdec($color[2] . $color[3]);
		$b = hexdec($color[4] . $color[5]);

		return (($r * 299 + $g * 587 + $b * 114) / 1_000 > $lighterThan);
	}

	public function isDark($color = false, int $darkerThan = 130): bool
	{
		$color = ($color) ? $color : $this->_hex;

		$r = hexdec($color[0] . $color[1]);
		$g = hexdec($color[2] . $color[3]);
		$b = hexdec($color[4] . $color[5]);

		return (($r * 299 + $g * 587 + $b * 114) / 1_000 <= $darkerThan);
	}

	public function complementary(): string
	{
		$hsl = $this->_hsl;
		$hsl['H'] += ($hsl['H'] > 180) ? -180 : 180;
		return Colors::hslToHex($hsl);
	}

	public function getHsl(): array
	{
		return $this->_hsl;
	}

	public function getHex(): string
	{
		return $this->_hex;
	}

	public function getRgb(): array
	{
		return $this->_rgb;
	}

	public function getCssGradient($amount = self::DEFAULT_ADJUST, $vintageBrowsers = false, $suffix = "", $prefix = ""): string
	{
		$g = $this->makeGradient($amount);
		$css = "";
		$css .= "{$prefix}background-color: #" . $this->_hex . ";{$suffix}";
		$css .= "{$prefix}filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#" . $g['light'] . "', endColorstr='#" . $g['dark'] . "');{$suffix}";
		if ($vintageBrowsers) {
			$css .= "{$prefix}background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#" . $g['light'] . "), to(#" . $g['dark'] . "));{$suffix}";
		}
		$css .= "{$prefix}background-image: -webkit-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
		if ($vintageBrowsers) {
			$css .= "{$prefix}background-image: -moz-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
			$css .= "{$prefix}background-image: -o-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
		}
		$css .= "{$prefix}background-image: linear-gradient(to bottom, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
		return $css;
	}

	private function darkenHsl(array $hsl, int $amount = self::DEFAULT_ADJUST): array
	{
		if ($amount) {
			$hsl['L'] = ($hsl['L'] * 100) - $amount;
			$hsl['L'] = ($hsl['L'] < 0) ? 0 : $hsl['L'] / 100;
		} else {
			$hsl['L'] /= 2;
		}
		return $hsl;
	}

	private function lightenHsl(array $hsl, int $amount = self::DEFAULT_ADJUST): array
	{
		if ($amount) {
			$hsl['L'] = ($hsl['L'] * 100) + $amount;
			$hsl['L'] = ($hsl['L'] > 100) ? 1 : $hsl['L'] / 100;
		} else {
			$hsl['L'] += (1 - $hsl['L']) / 2;
		}
		return $hsl;
	}

	private function mixRgb(array $rgb1, array $rgb2, int $amount = 0): array
	{
		$r1 = ($amount + 100) / 100;
		$r2 = 2 - $r1;

		$rmix = (($rgb1['R'] * $r1) + ($rgb2['R'] * $r2)) / 2;
		$gmix = (($rgb1['G'] * $r1) + ($rgb2['G'] * $r2)) / 2;
		$bmix = (($rgb1['B'] * $r1) + ($rgb2['B'] * $r2)) / 2;

		return array('R' => $rmix, 'G' => $gmix, 'B' => $bmix);
	}

	public function __toString()
	{
		return "#" . $this->getHex();
	}

	public function __get(string $name)
	{
		switch (strtolower($name)) {
			case 'red':
			case 'r':
				return $this->_rgb["R"];
			case 'green':
			case 'g':
				return $this->_rgb["G"];
			case 'blue':
			case 'b':
				return $this->_rgb["B"];
			case 'hue':
			case 'h':
				return $this->_hsl["H"];
			case 'saturation':
			case 's':
				return $this->_hsl["S"];
			case 'lightness':
			case 'l':
				return $this->_hsl["L"];
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);
		return null;
	}

	public function __set(string $name, $value)
	{
		switch (strtolower($name)) {
			case 'red':
			case 'r':
				$this->_rgb["R"] = $value;
				$this->_hex = Colors::rgbToHex($this->_rgb);
				$this->_hsl = Colors::hexToHsl($this->_hex);
				break;
			case 'green':
			case 'g':
				$this->_rgb["G"] = $value;
				$this->_hex = Colors::rgbToHex($this->_rgb);
				$this->_hsl = Colors::hexToHsl($this->_hex);
				break;
			case 'blue':
			case 'b':
				$this->_rgb["B"] = $value;
				$this->_hex = Colors::rgbToHex($this->_rgb);
				$this->_hsl = Colors::hexToHsl($this->_hex);
				break;
			case 'hue':
			case 'h':
				$this->_hsl["H"] = $value;
				$this->_hex = Colors::hslToHex($this->_hsl);
				$this->_rgb = Colors::hexToRgb($this->_hex);
				break;
			case 'saturation':
			case 's':
				$this->_hsl["S"] = $value;
				$this->_hex = Colors::hslToHex($this->_hsl);
				$this->_rgb = Colors::hexToRgb($this->_hex);
				break;
			case 'lightness':
			case 'light':
			case 'l':
				$this->_hsl["L"] = $value;
				$this->_hex = Colors::hslToHex($this->_hsl);
				$this->_rgb = Colors::hexToRgb($this->_hex);
				break;
		}
	}
}
