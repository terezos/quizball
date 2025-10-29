# AI Answer Validation

Το QuizBall υποστηρίζει **AI-powered answer validation** που δέχεται:

- ✅ **Ορθογραφικά λάθη** (π.χ. "Messy" αντί για "Messi")
- ✅ **Διαφορετικές γλώσσες** (π.χ. "Μέσι" αντί για "Messi")
- ✅ **Συντομογραφίες** (π.χ. "CR7" αντί για "Cristiano Ronaldo")
- ✅ **Παραλλαγές** (π.χ. "Real Madrid CF" αντί για "Real Madrid")

## Ενεργοποίηση

### 1. Πάρε OpenAI API Key

1. Πήγαινε στο https://platform.openai.com/api-keys
2. Δημιούργησε ένα API key
3. Πρόσθεσέ το στο `.env`:

```env
OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### 2. Ρυθμίσεις (προαιρετικό)

Στο `.env` μπορείς να ρυθμίσεις:

```env
# Ενεργοποίηση/Απενεργοποίηση AI validation
AI_VALIDATION_ENABLED=true

# Confidence threshold (0-1). Όσο πιο κοντά στο 1, τόσο πιο strict
AI_CONFIDENCE_THRESHOLD=0.85

# Μοντέλο OpenAI
AI_MODEL=gpt-4o-mini  # Φτηνό και γρήγορο
# AI_MODEL=gpt-4o     # Πιο ακριβές αλλά πιο ακριβό
```

## Πως Λειτουργεί

### Βήμα 1: Exact Match
Πρώτα προσπαθεί να βρει ακριβές match (case-insensitive):

```
Σωστή απάντηση: "Lionel Messi"
User έγραψε: "lionel messi"
✅ ΣΩΣΤΟ (exact match)
```

### Βήμα 2: AI Validation
Αν δεν υπάρχει exact match, ρωτάει το AI:

```
Σωστή απάντηση: "Lionel Messi"
User έγραψε: "Messy"
✅ ΣΩΣΤΟ (AI: 92% confidence - typo)
```

```
Σωστή απάντηση: "Lionel Messi"
User έγραψε: "Μέσι"
✅ ΣΩΣΤΟ (AI: 95% confidence - Greek name)
```

```
Σωστή απάντηση: "Cristiano Ronaldo"
User έγραψε: "CR7"
✅ ΣΩΣΤΟ (AI: 98% confidence - nickname)
```

```
Σωστή απάντηση: "Lionel Messi"
User έγραψε: "Ronaldo"
❌ ΛΑΘΟΣ (AI: Different player)
```

## Κόστος

Χρησιμοποιεί το **gpt-4o-mini** που είναι πολύ φτηνό:

- **Input:** $0.150 / 1M tokens
- **Output:** $0.600 / 1M tokens

Κάθε validation κοστίζει περίπου **$0.0001** (0.01 λεπτά του ευρώ).

**Παράδειγμα:** 10,000 ερωτήσεις = ~€1

## Απενεργοποίηση

Αν θες να το απενεργοποιήσεις:

```env
AI_VALIDATION_ENABLED=false
```

Ή απλά μην βάλεις `OPENAI_API_KEY` - θα λειτουργεί με exact match μόνο.

## Testing

Μπορείς να δοκιμάσεις το AI validation:

```bash
php artisan tinker
```

```php
$service = app(\App\Services\AIAnswerValidationService::class);

$result = $service->validateAnswer(
    question: "Who won the 2022 World Cup?",
    correctAnswers: ["Argentina"],
    userAnswer: "Αργεντινή"  // Greek
);

dd($result);
// [
//   "is_correct" => true,
//   "confidence" => 0.98,
//   "matched_answer" => "Argentina",
//   "reasoning" => "Greek translation of Argentina"
// ]
```

## Παραδείγματα που δουλεύουν

| Σωστή Απάντηση | User Input | Αποτέλεσμα |
|----------------|------------|------------|
| Lionel Messi | Messy | ✅ Typo |
| Lionel Messi | Μέσι | ✅ Greek |
| Cristiano Ronaldo | CR7 | ✅ Nickname |
| Manchester United | Man Utd | ✅ Abbreviation |
| Real Madrid | Real Madrid CF | ✅ Full name |
| FC Barcelona | Barca | ✅ Short name |
| Champions League | UCL | ✅ Abbreviation |

## Troubleshooting

### "AI validation failed, falling back to exact match"

Το AI validation έπεσε αλλά το game συνεχίζει κανονικά. Check:

1. Έχεις βάλει σωστό `OPENAI_API_KEY`;
2. Έχεις credits στο OpenAI account σου;
3. Check τα logs: `storage/logs/laravel.log`

### Δεν δουλεύει καθόλου

```bash
# Test OpenAI connection
php artisan tinker
```

```php
\OpenAI\Laravel\Facades\OpenAI::chat()->create([
    'model' => 'gpt-4o-mini',
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);
```

Αν βγάλει error, πρόβλημα με το API key.

## Επιπλέον Πληροφορίες

- Το AI validation χρησιμοποιεί **semantic matching** - καταλαβαίνει το νόημα
- Δουλεύει για **όλες τις γλώσσες** (Ελληνικά, Αγγλικά, Ισπανικά, κλπ)
- **Fallback:** Αν το AI πέσει, χρησιμοποιεί exact match
- **Cached:** Θα μπορούσαμε να cache τα results για γνωστές παραλλαγές

## Advanced: Custom Threshold ανά Question

Μπορείς να ορίσεις διαφορετικό threshold για δύσκολες ερωτήσεις:

```php
// Στο QuestionService
$result = $this->aiValidation->validateAnswer(
    $question->question_text,
    $correctAnswers,
    $playerAnswer,
    threshold: $question->difficulty === 'hard' ? 0.90 : 0.85
);
```

---

**Made with ❤️ for football fans who can't spell 😄**
