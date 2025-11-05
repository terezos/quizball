<?php

namespace App\Enums;

enum QuestionType: string
{
    case TextInput = 'text_input';
    case MultipleChoice = 'multiple_choice';
//    case TopFive = 'top_5';
    case TextInputWithImage = 'text_input_with_image';

    public function label(): string
    {
        return match($this) {
            self::TextInput => 'Συμπλήρωση κενού',
            self::MultipleChoice => 'Πολλαπλής επιλογής',
//            self::TopFive => 'Top 5',
            self::TextInputWithImage => 'Συμπλήρωση κενού με εικόνα',
        };
    }
}
