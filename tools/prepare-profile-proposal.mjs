import fs from 'node:fs';import {spawnSync} from 'node:child_process';
const path='app/Http/Controllers/PatientProfileController.php';
let source=fs.readFileSync(path,'utf8');
source=source.replace('        $user->update ($data);',`        $user->fill($data);
        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();
        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\\Throwable $exception) {
                \\Illuminate\\Support\\Facades\\Log::warning('Verification delivery failed', ['exception_type' => get_class($exception)]);
                return redirect()->route('verification.notice')->withErrors([
                    'email' => __('Your email was updated, but the verification message could not be sent. Please request another link.'),
                ]);
            }
            return redirect()->route('verification.notice');
        }`);
const proposed='storage/app/stabilization-20261121/proposed/'+path;
fs.writeFileSync(proposed,source);
fs.writeFileSync('docs/taqweem/PROFILE_VERIFICATION_PENDING.diff',spawnSync('git',['diff','--no-index','--',path,proposed],{encoding:'utf8'}).stdout);
