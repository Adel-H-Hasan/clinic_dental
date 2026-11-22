<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class GenerateSmileRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return [
        'image'=>['required','file','image','mimes:jpg,jpeg,png,webp','max:8192', function ($attribute, $file, $fail) {
            $size = @getimagesize($file->getRealPath());
            if (!$size || $size[0] < 160 || $size[1] < 100 || $size[0] * $size[1] > 16_000_000) {
                $fail(__('Use an image at least 160 × 100 pixels and no larger than 16 megapixels.'));
            }
        }],
        'mode'=>['required','in:face,mouth'],
        'treatment'=>['required','in:whitening,veneers,straightening,comprehensive'],
        'strength'=>['required','in:natural,moderate,bright'],
        'consent'=>['accepted'],
    ]; }
}
