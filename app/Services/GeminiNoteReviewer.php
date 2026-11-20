<?php

namespace App\Services;

use App\Models\MedicalNotes;
use Illuminate\Support\Facades\Validator;

class GeminiNoteReviewer
{
    public function review(MedicalNotes $note): ?array
    {
        $source = "التشخيص: " . $note->diagnosis
            . "\nالملاحظات: " . $note->notes
            . "\nموعد المتابعة: " . $note->next_visit;
        $noteText = implode("\n", [$note->diagnosis, $note->notes, $note->next_visit]);

        $instructions = <<<'PROMPT'
راجع التوثيق للطبيب بالعربية. ابحث عن معلومات ناقصة أو تعارض محتمل.
إذا ذكرت الملاحظة متابعة بلا موعد أو مدة، اسأل عن توقيتها تحديدًا واستشهد بالعبارة التي ذكرت الحاجة للمتابعة.
لكل اقتراح، اقتبس حرفيًا من القيم التي كتبها الطبيب واكتب سؤالًا قصيرًا. أسماء الحقول مثل «موعد المتابعة» وحدها ليست اقتباسًا.
استخدم النص أدناه كمصدر للمراجعة فقط. لا تضف تشخيصًا أو علاجًا.
إذا كانت الملاحظة واضحة، أعد قائمة اقتراحات فارغة.
PROMPT;

        $suggestion = [
            'type' => 'object',
            'properties' => [
                'kind' => ['type' => 'string', 'enum' => [
                    'missing_information', 'possible_conflict',
                ]],
                'evidence' => ['type' => 'array', 'items' => ['type' => 'string']],
                'question' => ['type' => 'string'],
            ],
            'required' => ['kind', 'evidence', 'question'],
        ];

        $result = (new GeminiClient())->generateJson($instructions . "\n\n" . $source, [
            'type' => 'object',
            'properties' => [
                'suggestions' => ['type' => 'array', 'items' => $suggestion],
            ],
            'required' => ['suggestions'],
        ]);

        if (!is_array($result) || Validator::make($result, [
            'suggestions' => 'present|array',
            'suggestions.*.kind' => 'required|in:missing_information,possible_conflict',
            'suggestions.*.evidence' => 'required|array|min:1',
            'suggestions.*.evidence.*' => 'required|string',
            'suggestions.*.question' => 'required|string',
        ])->fails()) {
            return null;
        }

        $suggestions = [];

        foreach ($result['suggestions'] as $item) {
            $matchesSource = true;

            foreach ($item['evidence'] as $quote) {
                if (!str_contains($noteText, $quote)) {
                    $matchesSource = false;
                }
            }

            if ($matchesSource) {
                $suggestions[] = $item;
            }
        }

        return $suggestions;
    }
}
