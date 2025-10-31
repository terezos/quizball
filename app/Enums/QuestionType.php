<?php

namespace App\Enums;

enum QuestionType: string
{
    case TextInput = 'text_input';
    case MultipleChoice = 'multiple_choice';
    case TopFive = 'top_5';
    case TextInputWithImage = 'text_input_with_image';
}
