<?php

namespace App\Livewire\Auth;
use App\Lib\Auth\PasswordAuthClass;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;


class PasswordLogin extends Component
{
    public string $user = '';
    public string $password = '';
    public string $captcha = '';
    public bool $rule = false;

    // We make num1 and num2 public so they persist across requests for the view.
    public int $num1;
    public int $num2;

    /**
     * Prepare the component state. This runs only on the initial load.
     */
    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirectRoute('dashboard.index');
        }
        $this->generateCaptcha();
    }

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'user' => ['required'],
            'password' => ['required', 'min:4'],
            'rule' => ['accepted'],
        ];
    }

    /**
     * Real-time validation messages (in Persian).
     */
    protected function validationAttributes(): array
    {
        return [
            'user' => 'شماره موبایل یا ایمیل',
            'password' => 'رمزعبور',
            'captcha' => 'پاسخ سوال امنیتی',
            'rule' => 'قوانین',
        ];
    }

    /**
     * Generate a new captcha question and store the answer securely in the session.
     */
    public function generateCaptcha(): void
    {
        $this->num1 = rand(1, 10);
        $this->num2 = rand(1, 100);

        // Store the correct answer in the session, not in a property.
        session()->put('captcha_answer', $this->num1 + $this->num2);

        $this->reset('captcha'); // Clear previous user input for captcha
    }

    /**
     * Handle the login attempt.
     */
    public function login()
    {
        // 1. Rate Limiting
        $throttleKey = strtolower($this->user) . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->dispatch('toast', text: "تعداد تلاش بیش از حد مجاز. لطفاً پس از {$seconds} ثانیه دوباره تلاش کنید.", icon: 'error');
            return;
        }

        // 2. Get the correct answer from the session
        $correctAnswer = session('captcha_answer');

        // 3. Validation
        try {
            $this->validate(array_merge($this->rules(), [
                'captcha' => [
                    'required',
                    'numeric',
                    // Use the session value for comparison
                    function ($attribute, $value, $fail) use ($correctAnswer) {
                        if (empty($correctAnswer) || (int)$value !== $correctAnswer) {
                            $fail('پاسخ سوال امنیتی صحیح نیست.');
                        }
                    },
                ],
            ]));
        } catch (ValidationException $e) {
            RateLimiter::hit($throttleKey);
            $this->generateCaptcha(); // Generate a new question on failure
            throw $e;
        }

        // 4. Authentication Logic
        try {
            $passwordAuth = new PasswordAuthClass($this->user, $this->password);
            $passwordAuth->login();

            RateLimiter::clear($throttleKey);
            session()->forget('captcha_answer'); // Clear the answer on success

            $this->dispatch('toast', text: 'ورود با موفقیت انجام شد', icon: 'success', timeout: 2000);
            $this->redirectRoute('dashboard.index', navigate: true);

        } catch (\Exception $e) {
            RateLimiter::hit($throttleKey);
            $this->generateCaptcha(); // Generate a new question on failure
            $this->dispatch('toast', text: $e->getMessage(), icon: 'error');
        }
    }

    /**
     * Render the component.
     */
    #[Layout('components.layouts.auth')]
    public function render()
    {
        // The captcha question is now created from the public properties
        return view('livewire.auth.password-login', [
            'captchaQuestion' => "جمع {$this->num1} + {$this->num2} چند میشود؟"
        ]);
    }
}
