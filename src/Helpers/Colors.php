<?php
declare(strict_types=1);

namespace Developion\Toolbox\Helpers;

class Colors
{
	/**
	 * @param string $color
	 * @return array{'H': number, 'S': number, 'L': number}
	 */
	public static function hexToHsl(string $color): array
	{
		$color = self::sanitizeHex($color);

		$R = hexdec($color[0] . $color[1]);
		$G = hexdec($color[2] . $color[3]);
		$B = hexdec($color[4] . $color[5]);

		$HSL = array();

		$var_R = ($R / 255);
		$var_G = ($G / 255);
		$var_B = ($B / 255);

		$var_Min = min($var_R, $var_G, $var_B);
		$var_Max = max($var_R, $var_G, $var_B);
		$del_Max = $var_Max - $var_Min;

		$L = ($var_Max + $var_Min) / 2;

		if ($del_Max == 0) {
			$H = 0;
			$S = 0;
		} else {
			if ($L < 0.5) {
				$S = $del_Max / ($var_Max + $var_Min);
			} else {
				$S = $del_Max / (2 - $var_Max - $var_Min);
			}

			$del_R = ((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
			$del_G = ((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
			$del_B = ((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;

			if ($var_R == $var_Max) {
				$H = $del_B - $del_G;
			} elseif ($var_G == $var_Max) {
				$H = (1 / 3) + $del_R - $del_B;
			} elseif ($var_B == $var_Max) {
				$H = (2 / 3) + $del_G - $del_R;
			}

			if ($H < 0) {
				$H++;
			}
			if ($H > 1) {
				$H--;
			}
		}

		$HSL['H'] = ($H * 360);
		$HSL['S'] = $S;
		$HSL['L'] = $L;

		return $HSL;
	}

	public static function hslToHex(array $hsl = array()): string
	{
		if (empty($hsl) || !isset($hsl["H"], $hsl["S"], $hsl["L"])) {
			throw new \Exception("Param was not an HSL array");
		}

		list($H, $S, $L) = array($hsl['H'] / 360, $hsl['S'], $hsl['L']);

		if ($S == 0) {
			$r = $L * 255;
			$g = $L * 255;
			$b = $L * 255;
		} else {
			if ($L < 0.5) {
				$var_2 = $L * (1 + $S);
			} else {
				$var_2 = ($L + $S) - ($S * $L);
			}

			$var_1 = 2 * $L - $var_2;

			$r = 255 * self::hueToRgb($var_1, $var_2, $H + (1 / 3));
			$g = 255 * self::hueToRgb($var_1, $var_2, $H);
			$b = 255 * self::hueToRgb($var_1, $var_2, $H - (1 / 3));
		}

		$r = dechex(intval(round($r)));
		$g = dechex(intval(round($g)));
		$b = dechex(intval(round($b)));

		$r = (strlen("" . $r) === 1) ? "0" . $r : $r;
		$g = (strlen("" . $g) === 1) ? "0" . $g : $g;
		$b = (strlen("" . $b) === 1) ? "0" . $b : $b;

		return $r . $g . $b;
	}

	public static function hexToRgb(string $color): array
	{
		$color = self::sanitizeHex($color);

		$R = hexdec($color[0] . $color[1]);
		$G = hexdec($color[2] . $color[3]);
		$B = hexdec($color[4] . $color[5]);

		$RGB['R'] = $R;
		$RGB['G'] = $G;
		$RGB['B'] = $B;

		return $RGB;
	}

	public static function rgbToHex(array $rgb = array()): string
	{
		if (empty($rgb) || !isset($rgb["R"], $rgb["G"], $rgb["B"])) {
			throw new \Exception("Param was not an RGB array");
		}

		$hex[0] = str_pad(dechex((int)$rgb['R']), 2, '0', STR_PAD_LEFT);
		$hex[1] = str_pad(dechex((int)$rgb['G']), 2, '0', STR_PAD_LEFT);
		$hex[2] = str_pad(dechex((int)$rgb['B']), 2, '0', STR_PAD_LEFT);

		$hex[0] = (strlen($hex[0]) === 1) ? '0' . $hex[0] : $hex[0];
		$hex[1] = (strlen($hex[1]) === 1) ? '0' . $hex[1] : $hex[1];
		$hex[2] = (strlen($hex[2]) === 1) ? '0' . $hex[2] : $hex[2];

		return implode('', $hex);
	}

	public static function rgbToString(array $rgb = array()): string
	{
		if (empty($rgb) || !isset($rgb["R"], $rgb["G"], $rgb["B"])) {
			throw new \Exception("Param was not an RGB array");
		}

		return 'rgb(' .
			$rgb['R'] . ', ' .
			$rgb['G'] . ', ' .
			$rgb['B'] . ')';
	}

	public static function nameToHex(string $color_name): string
	{
		$colors = array(
			'aliceblue' => 'F0F8FF',
			'antiquewhite' => 'FAEBD7',
			'aqua' => '00FFFF',
			'aquamarine' => '7FFFD4',
			'azure' => 'F0FFFF',
			'beige' => 'F5F5DC',
			'bisque' => 'FFE4C4',
			'black' => '000000',
			'blanchedalmond' => 'FFEBCD',
			'blue' => '0000FF',
			'blueviolet' => '8A2BE2',
			'brown' => 'A52A2A',
			'burlywood' => 'DEB887',
			'cadetblue' => '5F9EA0',
			'chartreuse' => '7FFF00',
			'chocolate' => 'D2691E',
			'coral' => 'FF7F50',
			'cornflowerblue' => '6495ED',
			'cornsilk' => 'FFF8DC',
			'crimson' => 'DC143C',
			'cyan' => '00FFFF',
			'darkblue' => '00008B',
			'darkcyan' => '008B8B',
			'darkgoldenrod' => 'B8860B',
			'darkgray' => 'A9A9A9',
			'darkgreen' => '006400',
			'darkgrey' => 'A9A9A9',
			'darkkhaki' => 'BDB76B',
			'darkmagenta' => '8B008B',
			'darkolivegreen' => '556B2F',
			'darkorange' => 'FF8C00',
			'darkorchid' => '9932CC',
			'darkred' => '8B0000',
			'darksalmon' => 'E9967A',
			'darkseagreen' => '8FBC8F',
			'darkslateblue' => '483D8B',
			'darkslategray' => '2F4F4F',
			'darkslategrey' => '2F4F4F',
			'darkturquoise' => '00CED1',
			'darkviolet' => '9400D3',
			'deeppink' => 'FF1493',
			'deepskyblue' => '00BFFF',
			'dimgray' => '696969',
			'dimgrey' => '696969',
			'dodgerblue' => '1E90FF',
			'firebrick' => 'B22222',
			'floralwhite' => 'FFFAF0',
			'forestgreen' => '228B22',
			'fuchsia' => 'FF00FF',
			'gainsboro' => 'DCDCDC',
			'ghostwhite' => 'F8F8FF',
			'gold' => 'FFD700',
			'goldenrod' => 'DAA520',
			'gray' => '808080',
			'green' => '008000',
			'greenyellow' => 'ADFF2F',
			'grey' => '808080',
			'honeydew' => 'F0FFF0',
			'hotpink' => 'FF69B4',
			'indianred' => 'CD5C5C',
			'indigo' => '4B0082',
			'ivory' => 'FFFFF0',
			'khaki' => 'F0E68C',
			'lavender' => 'E6E6FA',
			'lavenderblush' => 'FFF0F5',
			'lawngreen' => '7CFC00',
			'lemonchiffon' => 'FFFACD',
			'lightblue' => 'ADD8E6',
			'lightcoral' => 'F08080',
			'lightcyan' => 'E0FFFF',
			'lightgoldenrodyellow' => 'FAFAD2',
			'lightgray' => 'D3D3D3',
			'lightgreen' => '90EE90',
			'lightgrey' => 'D3D3D3',
			'lightpink' => 'FFB6C1',
			'lightsalmon' => 'FFA07A',
			'lightseagreen' => '20B2AA',
			'lightskyblue' => '87CEFA',
			'lightslategray' => '778899',
			'lightslategrey' => '778899',
			'lightsteelblue' => 'B0C4DE',
			'lightyellow' => 'FFFFE0',
			'lime' => '00FF00',
			'limegreen' => '32CD32',
			'linen' => 'FAF0E6',
			'magenta' => 'FF00FF',
			'maroon' => '800000',
			'mediumaquamarine' => '66CDAA',
			'mediumblue' => '0000CD',
			'mediumorchid' => 'BA55D3',
			'mediumpurple' => '9370D0',
			'mediumseagreen' => '3CB371',
			'mediumslateblue' => '7B68EE',
			'mediumspringgreen' => '00FA9A',
			'mediumturquoise' => '48D1CC',
			'mediumvioletred' => 'C71585',
			'midnightblue' => '191970',
			'mintcream' => 'F5FFFA',
			'mistyrose' => 'FFE4E1',
			'moccasin' => 'FFE4B5',
			'navajowhite' => 'FFDEAD',
			'navy' => '000080',
			'oldlace' => 'FDF5E6',
			'olive' => '808000',
			'olivedrab' => '6B8E23',
			'orange' => 'FFA500',
			'orangered' => 'FF4500',
			'orchid' => 'DA70D6',
			'palegoldenrod' => 'EEE8AA',
			'palegreen' => '98FB98',
			'paleturquoise' => 'AFEEEE',
			'palevioletred' => 'DB7093',
			'papayawhip' => 'FFEFD5',
			'peachpuff' => 'FFDAB9',
			'peru' => 'CD853F',
			'pink' => 'FFC0CB',
			'plum' => 'DDA0DD',
			'powderblue' => 'B0E0E6',
			'purple' => '800080',
			'red' => 'FF0000',
			'rosybrown' => 'BC8F8F',
			'royalblue' => '4169E1',
			'saddlebrown' => '8B4513',
			'salmon' => 'FA8072',
			'sandybrown' => 'F4A460',
			'seagreen' => '2E8B57',
			'seashell' => 'FFF5EE',
			'sienna' => 'A0522D',
			'silver' => 'C0C0C0',
			'skyblue' => '87CEEB',
			'slateblue' => '6A5ACD',
			'slategray' => '708090',
			'slategrey' => '708090',
			'snow' => 'FFFAFA',
			'springgreen' => '00FF7F',
			'steelblue' => '4682B4',
			'tan' => 'D2B48C',
			'teal' => '008080',
			'thistle' => 'D8BFD8',
			'tomato' => 'FF6347',
			'turquoise' => '40E0D0',
			'violet' => 'EE82EE',
			'wheat' => 'F5DEB3',
			'white' => 'FFFFFF',
			'whitesmoke' => 'F5F5F5',
			'yellow' => 'FFFF00',
			'yellowgreen' => '9ACD32',
		);

		$color_name = strtolower($color_name);
		if (isset($colors[$color_name])) {
			return '#' . $colors[$color_name];
		}

		return $color_name;
	}

	public static function hueToRgb(float $v1, float $v2, float $vH): float
	{
		if ($vH < 0) {
			++$vH;
		}

		if ($vH > 1) {
			--$vH;
		}

		if ((6 * $vH) < 1) {
			return ($v1 + ($v2 - $v1) * 6 * $vH);
		}

		if ((2 * $vH) < 1) {
			return $v2;
		}

		if ((3 * $vH) < 2) {
			return ($v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6);
		}

		return $v1;
	}

	public static function sanitizeHex(string $hex): string
	{
		$color = str_replace("#", "", $hex);

		if (!preg_match('/^[a-fA-F0-9]+$/', $color)) {
			throw new \Exception("HEX color does not match format");
		}

		if (strlen($color) === 3) {
			$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
		} elseif (strlen($color) !== 6) {
			throw new \Exception("HEX color needs to be 6 or 3 digits long");
		}

		return $color;
	}
}
