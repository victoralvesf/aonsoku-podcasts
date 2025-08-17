<?php

namespace App\Form;

use Filament\Forms\Components\RichEditor;

class TextEditor extends RichEditor
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'link'],
                ['h1', 'h2', 'h3', 'alignStart', 'alignCenter', 'alignEnd'],
                ['bulletList', 'orderedList', 'horizontalRule'],
                ['undo', 'redo'],
            ]);
    }
}
