<?php namespace Chitanka\Utils;

class Typograph {

	public static $maxQuoteIterations = 6;

	/**
	 * Replace some characters and generally prettify content
	 * @param string $cont
	 * @return string
	 */
	public static function replaceAll($cont) {
		$chars = ["\r" => '',
			'„' => '"', '“' => '"', '”' => '"', '«' => '"', '»' => '"', '&quot;' => '"',
			'&bdquo;' => '"', '&ldquo;' => '"', '&rdquo;' => '"', '&laquo;' => '"',
			'&raquo;' => '"', '&#132;' => '"', '&#147;' => '"', '&#148;' => '"',
			'&lt;' => '&amp;lt;', '&gt;' => '&amp;gt;', '&nbsp;' => '&amp;nbsp;',
			"'" => '’', '...' => '…',
			'</p>' => '', '</P>' => '',
			'<p>' => "\n\t", '<P>' => "\n\t",
		];
		$reg_chars = [
			'/(\d)x(\d)/' => '$1×$2', # знак за умножение
			'/\n +/' => "\n\t", # абзаци
			'/(?<!\n)\n\t\* \* \*\n(?!\n)/' => "\n\n\t* * *\n\n",
		];

		$cont = preg_replace('/([\s(]\d+ *)-( *\d+[\s),.])/', '$1–$2', "\n".$cont);
		$cont = str_replace(array_keys($chars), array_values($chars), $cont);
		$cont = self::replaceDash($cont);
		$cont = preg_replace(array_keys($reg_chars), array_values($reg_chars), $cont);

		# кавички
		$qreg = '/(?<=[([\s|\'"_\->\/])"(\S?|\S[^"]*[^\s"([])"/m';
		$i = 0;
		while ( strpos($cont, '"') !== false ) {
			if ( ++$i > self::$maxQuoteIterations ) {
				error_log("Typograph: Вероятна грешка: Повече от ".self::$maxQuoteIterations." итерации при вътрешните кавички.");
				break;
			}
			$cont = preg_replace_callback($qreg, function($matches) {
				return '„'. strtr($matches[1], ['„'=>'«', '“'=>'»', '«'=>'„', '»'=>'“']) .'“';
			}, $cont);
		}

		return ltrim($cont, "\n");
	}

	public static function replaceTimesChar($string) {
		return preg_replace('/(\d) ?[xXхХ] ?(\d)/u', '$1×$2', $string);
	}

	public static function replaceDash($string) {
		$map = [
			'/(\s)(-|–|­){1,2}(\s)/' => '$1—$3', # mdash
			'/([\s(][\d,.]*)-([\d,.]+[\s)])/' => '$1–$2', # ndash между цифри
		];
		return preg_replace(array_keys($map), array_values($map), $string);
	}
}
