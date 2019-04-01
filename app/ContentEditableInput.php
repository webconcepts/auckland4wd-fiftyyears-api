<?php

namespace App;

class ContentEditableInput
{
    public function sanitise($value, $allowedTags = '')
    {
        return $this->stripAttributes(
            strip_tags(html_entity_decode($value), $allowedTags)
        );
    }

    /**
     * @author Greg Randall
     * @see https://stackoverflow.com/a/50538153
     */
    protected function stripAttributes($string)
    {
        // get all html elements on a line by themselves
        $tagsOnSeparateLines = str_replace(["<", ">"], ["\n<", ">\n"], $string);

        // find lines starting with a '<' and any letters or numbers upto the
        // first space. throw everything after the space away.
        $withoutAttributes = preg_replace("/\n(<[\w123456]+)\s.+/i", "\n$1>", $tagsOnSeparateLines);

        return str_replace("\n", '', $withoutAttributes);
    }
}
