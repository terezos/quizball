<?php

namespace App;

enum QuestionType: string
{
    case TextInput = 'text_input';
    case MultipleChoice = 'multiple_choice';
    case TopFive = 'top_5';
}
