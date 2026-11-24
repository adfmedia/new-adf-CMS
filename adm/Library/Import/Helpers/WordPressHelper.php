<?php

namespace Adm\Library\Import\Helpers;

class WordPressHelper
{
    /**
     * Local instance of HTML split regex for frequent use.
     * 
     * @var ?string
     */
    protected static $html_split_regex  = null;

    /**
     * Function taken from WordPress to replace double line breaks 
     * with paragraph elements.
     * Source - https://developer.wordpress.org/reference/functions/wpautop/
     * 
     * @param string $text
     * @param bool $br
     * @return string
     */
    public static function wp_auto_p(string $text, bool $br = true): string
    {
        $pre_tags = array();

        if (trim($text) === '') {
            return '';
        }

        // Just to make things a little easier, pad the end.
        $text = $text . "\n";

        /*
        * Pre tags shouldn't be touched by autop.
        * Replace pre tags with placeholders and bring them back after autop.
        */
        if (str_contains($text, '<pre')) {
            $text_parts = explode('</pre>', $text);
            $last_part  = array_pop($text_parts);
            $text       = '';
            $i          = 0;

            foreach ($text_parts as $text_part) {
                $start = strpos($text_part, '<pre');

                // Malformed HTML?
                if (false === $start) {
                    $text .= $text_part;
                    continue;
                }

                $name              = "<pre wp-pre-tag-$i></pre>";
                $pre_tags[$name] = substr($text_part, $start) . '</pre>';

                $text .= substr($text_part, 0, $start) . $name;
                ++$i;
            }

            $text .= $last_part;
        }
        // Change multiple <br>'s into two line breaks, which will turn into paragraphs.
        $text = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $text);

        $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

        // Add a double line break above block-level opening tags.
        $text = preg_replace('!(<' . $allblocks . '[\s/>])!', "\n\n$1", $text);

        // Add a double line break below block-level closing tags.
        $text = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $text);

        // Add a double line break after hr tags, which are self closing.
        $text = preg_replace('!(<hr\s*?/?>)!', "$1\n\n", $text);

        // Standardize newline characters to "\n".
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        // Find newlines in all elements and add placeholders.
        $text = self::wp_replace_in_html_tags($text, array("\n" => ' <!-- wpnl --> '));

        // Collapse line breaks before and after <option> elements so they don't get autop'd.
        if (str_contains($text, '<option')) {
            $text = preg_replace('|\s*<option|', '<option', $text);
            $text = preg_replace('|</option>\s*|', '</option>', $text);
        }

        /*
        * Collapse line breaks inside <object> elements, before <param> and <embed> elements
        * so they don't get autop'd.
        */
        if (str_contains($text, '</object>')) {
            $text = preg_replace('|(<object[^>]*>)\s*|', '$1', $text);
            $text = preg_replace('|\s*</object>|', '</object>', $text);
            $text = preg_replace('%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $text);
        }

        /*
        * Collapse line breaks inside <audio> and <video> elements,
        * before and after <source> and <track> elements.
        */
        if (str_contains($text, '<source') || str_contains($text, '<track')) {
            $text = preg_replace('%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $text);
            $text = preg_replace('%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $text);
            $text = preg_replace('%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $text);
        }

        // Collapse line breaks before and after <figcaption> elements.
        if (str_contains($text, '<figcaption')) {
            $text = preg_replace('|\s*(<figcaption[^>]*>)|', '$1', $text);
            $text = preg_replace('|</figcaption>\s*|', '</figcaption>', $text);
        }

        // Remove more than two contiguous line breaks.
        $text = preg_replace("/\n\n+/", "\n\n", $text);

        // Split up the contents into an array of strings, separated by double line breaks.
        $paragraphs = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Reset $text prior to rebuilding.
        $text = '';

        // Rebuild the content as a string, wrapping every bit with a <p>.
        foreach ($paragraphs as $paragraph) {
            $text .= '<p>' . trim($paragraph, "\n") . "</p>\n";
        }

        // Under certain strange conditions it could create a P of entirely whitespace.
        $text = preg_replace('|<p>\s*</p>|', '', $text);

        // Add a closing <p> inside <div>, <address>, or <form> tag if missing.
        $text = preg_replace('!<p>([^<]+)</(div|address|form)>!', '<p>$1</p></$2>', $text);

        // If an opening or closing block element tag is wrapped in a <p>, unwrap it.
        $text = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $text);

        // In some cases <li> may get wrapped in <p>, fix them.
        $text = preg_replace('|<p>(<li.+?)</p>|', '$1', $text);

        // If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
        $text = preg_replace('|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $text);
        $text = str_replace('</blockquote></p>', '</p></blockquote>', $text);

        // If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
        $text = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', '$1', $text);

        // If an opening or closing block element tag is followed by a closing <p> tag, remove it.
        $text = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $text);

        // Optionally insert line breaks.
        if ($br) {
            // Replace newlines that shouldn't be touched with a placeholder.
            $text = preg_replace_callback('/<(script|style|svg|math).*?<\/\\1>/s', [self::class, '_autop_newline_preservation_helper'], $text);

            // Normalize <br>
            $text = str_replace(array('<br>', '<br/>'), '<br />', $text);

            // Replace any new line characters that aren't preceded by a <br /> with a <br />.
            $text = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $text);

            // Replace newline placeholders with newlines.
            $text = str_replace('<WPPreserveNewline />', "\n", $text);
        }

        // If a <br /> tag is after an opening or closing block tag, remove it.
        $text = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', '$1', $text);

        // If a <br /> tag is before a subset of opening or closing block tags, remove it.
        $text = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $text);
        $text = preg_replace("|\n</p>$|", '</p>', $text);

        // Replace placeholder <pre> tags with their original content.
        if (!empty($pre_tags)) {
            $text = str_replace(array_keys($pre_tags), array_values($pre_tags), $text);
        }

        // Restore newlines in all elements.
        if (str_contains($text, '<!-- wpnl -->')) {
            $text = str_replace(array(' <!-- wpnl --> ', '<!-- wpnl -->'), "\n", $text);
        }

        return $text;
    }

    /**
     * Function taken from WordPress to replace characters or phrases within
     * HTML elements only.
     * Source - https://developer.wordpress.org/reference/functions/wp_replace_in_html_tags/
     * 
     * @param string $haystack
     * @param array $replace_pairs
     * @return string
     */
    public static function wp_replace_in_html_tags(string $haystack, array $replace_pairs): string
    {
        // Find all elements.
        $textarr = self::wp_html_split($haystack);
        $changed = false;

        // Optimize when searching for one item.
        if (1 === count($replace_pairs)) {
            // Extract $needle and $replace.
            foreach ($replace_pairs as $needle => $replace) {
            }

            // Loop through delimiters (elements) only.
            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                if (str_contains($textarr[$i], $needle)) {
                    $textarr[$i] = str_replace($needle, $replace, $textarr[$i]);
                    $changed       = true;
                }
            }
        } else {
            // Extract all $needles.
            $needles = array_keys($replace_pairs);

            // Loop through delimiters (elements) only.
            for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                foreach ($needles as $needle) {
                    if (str_contains($textarr[$i], $needle)) {
                        $textarr[$i] = strtr($textarr[$i], $replace_pairs);
                        $changed       = true;
                        // After one strtr() break out of the foreach loop and look at next element.
                        break;
                    }
                }
            }
        }

        if ($changed) {
            $haystack = implode($textarr);
        }

        return $haystack;
    }

    /**
     * Function taken from WordPress to separate HTML elements and comments from text.
     * Source - https://developer.wordpress.org/reference/functions/wp_html_split/
     * 
     * @param string $input
     * @return array
     */
    public static function wp_html_split(string $input): array
    {
        return preg_split(self::get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * Function taken from WordPress to retrieve a regular expression for a HTML element.
     * Source - https://developer.wordpress.org/reference/functions/get_html_split_regex/
     * 
     * @return string
     */
    public static function get_html_split_regex(): string
    {
        if (is_null(self::$html_split_regex)) {
            // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
            $comments =
                '!'             // Start of comment, after the <.
                . '(?:'         // Unroll the loop: Consume everything until --> is found.
                .     '-(?!->)' // Dash not followed by end of comment.
                .     '[^\-]*+' // Consume non-dashes.
                . ')*+'         // Loop possessively.
                . '(?:-->)?';   // End of comment. If not found, match all input.

            $cdata =
                '!\[CDATA\['    // Start of comment, after the <.
                . '[^\]]*+'     // Consume non-].
                . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
                .     '](?!]>)' // One ] not followed by end of comment.
                .     '[^\]]*+' // Consume non-].
                . ')*+'         // Loop possessively.
                . '(?:]]>)?';   // End of comment. If not found, match all input.

            $escaped =
                '(?='             // Is the element escaped?
                .    '!--'
                . '|'
                .    '!\[CDATA\['
                . ')'
                . '(?(?=!-)'      // If yes, which type?
                .     $comments
                . '|'
                .     $cdata
                . ')';

            $regex =
                '/('                // Capture the entire match.
                .     '<'           // Find start of element.
                .     '(?'          // Conditional expression follows.
                .         $escaped  // Find end of escaped element.
                .     '|'           // ...else...
                .         '[^>]*>?' // Find end of normal element.
                .     ')'
                . ')/';
            // phpcs:enable

            self::$html_split_regex = $regex;
        }

        return self::$html_split_regex;
    }

    /**
     * Function taken from WordPress to preserve new lines in WYSIWYG content.
     * Source - https://developer.wordpress.org/reference/functions/_autop_newline_preservation_helper/
     * 
     * @param array $matches
     * @return string
     */
    public static function _autop_newline_preservation_helper(array $matches): string
    {
        return str_replace("\n", '<WPPreserveNewline />', $matches[0]);
    }

    /**
     * Replace WordPress caption shortcodes with their HTML
     * counterparts
     * 
     * @param string $content
     * @return string
     */
    public static function replace_caption_shortcodes(string $content): string
    {
        preg_match_all('/\[caption[^\]]*\](.*)\[\/caption\]/', $content, $caption_matches);
        if (!empty($caption_matches) && !empty($caption_matches[0]) && !empty($caption_matches[1])) {
            foreach ($caption_matches[0] as $match_idx => $shortcode) {
                $caption_contents = $caption_matches[1][$match_idx];
                $img_end_pos = strpos($caption_contents, '/>');

                if ($img_end_pos > 0) {
                    $img_el = substr($caption_contents, 0, $img_end_pos + 2);
                    $caption_el = substr($caption_contents, $img_end_pos + 2);

                    $figure_contents = '<figure>' . $img_el . '<figcaption>' . $caption_el . '</figcaption></figure>';

                    $content = str_replace($shortcode, $figure_contents, $content);
                }
            }
        }
        return $content;
    }
}