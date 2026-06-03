<?php

if (! function_exists('moveat_reviews_parse_topics')) {
	/**
	 * @return string[]
	 */
	function moveat_reviews_parse_topics($raw) {
		if (! is_string($raw) || $raw === '') {
			return [];
		}
		$parts = preg_split('/\|+/u', $raw);
		if (! is_array($parts)) {
			return [];
		}
		$out = [];
		foreach ($parts as $p) {
			$t = trim((string) $p);
			if ($t !== '') {
				$out[] = $t;
			}
		}
		return $out;
	}
}

if (! function_exists('moveat_reviews_parse_vocabulary_raw')) {
	/**
	 * Темы из поля «Общий список тем»: строки и/или разделитель |.
	 *
	 * @return string[]
	 */
	function moveat_reviews_parse_vocabulary_raw($raw) {
		if (! is_string($raw) || $raw === '') {
			return [];
		}
		$raw = str_replace(["\r\n", "\r"], "\n", $raw);
		$out = [];
		foreach (explode("\n", $raw) as $line) {
			$line = trim($line);
			if ($line === '') {
				continue;
			}
			foreach (moveat_reviews_parse_topics($line) as $piece) {
				if ($piece !== '' && ! in_array($piece, $out, true)) {
					$out[] = $piece;
				}
			}
		}
		return $out;
	}
}

if (! function_exists('moveat_reviews_topic_choices_from_vocabulary')) {
	/**
	 * @return array<string, string>
	 */
	function moveat_reviews_topic_choices_from_vocabulary($post_id) {
		if (! function_exists('get_field') || ! $post_id) {
			return [];
		}
		$raw  = get_field('reviews_topics_vocabulary', $post_id);
		$list = moveat_reviews_parse_vocabulary_raw(is_string($raw) ? $raw : '');
		$choices = [];
		foreach ($list as $topic) {
			$choices[$topic] = $topic;
		}
		return $choices;
	}
}

if (! function_exists('moveat_reviews_normalize_topics_field')) {
	/**
	 * Значение ACF: массив (checkbox) или строка (старый формат).
	 *
	 * @param mixed $topics
	 * @return string[]
	 */
	function moveat_reviews_normalize_topics_field($topics) {
		if (is_array($topics)) {
			$out = [];
			foreach ($topics as $t) {
				if (! is_string($t)) {
					continue;
				}
				$t = trim($t);
				if ($t !== '') {
					$out[] = $t;
				}
			}
			return $out;
		}
		if (is_string($topics) && $topics !== '') {
			return moveat_reviews_parse_topics($topics);
		}
		return [];
	}
}

if (! function_exists('moveat_reviews_topics_chips_attr')) {
	function moveat_reviews_topics_chips_attr($topics) {
		$list = moveat_reviews_normalize_topics_field($topics);
		return implode('|', $list);
	}
}
