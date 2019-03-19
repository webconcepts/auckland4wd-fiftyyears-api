<?php

namespace App;

class ContentEditableInput
{
    public function sanitise($value, $allowedTags = '')
    {
        return strip_tags(html_entity_decode($value), $allowedTags);
    }
}
