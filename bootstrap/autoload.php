<?php

function common_text(string $field): string
{
    return \App\Helpers\CommonTextHelper::getValue($field);
}
