<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class GenerateSmileRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return [
        'image'=>['required','file','mimes:jpg,jpeg,png,webp','max:8192'],
        'mode'=>['required','in:face,mouth'],
        'treatment'=>['required','in:whitening,veneers,straightening,comprehensive'],
        'strength'=>['required','in:natural,moderate,bright'],
        'consent'=>['accepted'],
    ]; }
}
