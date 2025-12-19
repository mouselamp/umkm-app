<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Siomay Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-display antialiased bg-background-light dark:bg-background-dark" x-data="{
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    showPassword: false,
    showConfirmPassword: false,
    agreeTerms: false,
    loading: false,
    error: null,
    errors: {},
    async register() {
        this.loading = true;
        this.error = null;
        this.errors = {};
        try {
            const response = await fetch('/api/v1/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ name: this.name, email: this.email, password: this.password, password_confirmation: this.password_confirmation })
            });
            const data = await response.json();
            if (response.ok) {
                localStorage.setItem('token', data.data.token);
                window.location.href = '/';
            } else if (response.status === 422) {
                this.errors = data.errors || {};
                this.error = data.message || 'Validation failed';
            } else {
                this.error = data.message || 'Registration failed';
            }
        } catch (e) { this.error = 'An error occurred'; }
        finally { this.loading = false; }
    }
}">
    <div class="min-h-screen flex w-full flex-row">
        <!-- Left Side: Hero Image -->
        <div class="hidden lg:flex w-1/2 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent z-10"></div>
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCHCOIRbzda1FPymK4CwHKPy5s0-PenX-Z80OILAauzs0_xVocTrjNPY6x50IRuGcmDCZFMo6e5odYrLbNyau9kg8IryJtnsKQegrcWliHp5FVrieaFk9LGUlxK0rQYvB8_6SQsOyRj72H1qZvNK9P8q6hlHk_nGpGqQLW9dNyPPpl0qa6ZEY6L0auvlCPOGNAB7-lYBgdeD-rinhMbIyylhLZWID6bAUN5kv-CaLrD_xOMlOQSyyVuK9w4nBN4oqvO2iEVFDNFB7Q" alt="Delicious Dimsum" class="absolute inset-0 h-full w-full object-cover opacity-90">
            <div class="relative z-20 flex flex-col justify-end p-16 h-full text-white">
                <div class="flex items-center gap-4 mb-6">
                    <div class="size-10 text-primary">
                        <svg class="w-full h-full" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M42.1739 20.1739L27.8261 5.82609C29.1366 7.13663 28.3989 10.1876 26.2002 13.7654C24.8538 15.9564 22.9595 18.3449 20.6522 20.6522C18.3449 22.9595 15.9564 24.8538 13.7654 26.2002C10.1876 28.3989 7.13663 29.1366 5.82609 27.8261L20.1739 42.1739C21.4845 43.4845 24.5355 42.7467 28.1133 40.548C30.3042 39.2016 32.6927 37.3073 35 35C37.3073 32.6927 39.2016 30.3042 40.548 28.1133C42.7467 24.5355 43.4845 21.4845 42.1739 20.1739Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Siomay Manager</h1>
                </div>
                <blockquote class="text-xl font-medium leading-relaxed max-w-lg">
                    "Streamline your kitchen, track every sale, and grow your dimsum business with confidence."
                </blockquote>
            </div>
        </div>
        <!-- Right Side: Registration Form -->
        <div class="flex-1 flex flex-col justify-center items-center p-6 lg:p-12 relative">
            <!-- Mobile Logo -->
            <div class="lg:hidden absolute top-6 left-6 flex items-center gap-3 mb-8">
                <div class="size-8 text-primary">
                    <svg class="w-full h-full" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M42.1739 20.1739L27.8261 5.82609C29.1366 7.13663 28.3989 10.1876 26.2002 13.7654C24.8538 15.9564 22.9595 18.3449 20.6522 20.6522C18.3449 22.9595 15.9564 24.8538 13.7654 26.2002C10.1876 28.3989 7.13663 29.1366 5.82609 27.8261L20.1739 42.1739C21.4845 43.4845 24.5355 42.7467 28.1133 40.548C30.3042 39.2016 32.6927 37.3073 35 35C37.3073 32.6927 39.2016 30.3042 40.548 28.1133C42.7467 24.5355 43.4845 21.4845 42.1739 20.1739Z" fill="currentColor"/>
                    </svg>
                </div>
                <span class="font-bold text-lg text-text-main-light dark:text-white">Siomay Manager</span>
            </div>
            <div class="w-full max-w-[480px] bg-white dark:bg-[#1a2c26] rounded-xl shadow-sm p-8 sm:p-10 border border-gray-100 dark:border-gray-800">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl sm:text-3xl font-bold text-text-main-light dark:text-white tracking-tight mb-3">Create your Account</h2>
                    <p class="text-text-sub-light dark:text-gray-400 text-base">Start managing your Siomay production today.</p>
                </div>
                <div x-show="error" class="mb-4 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-3 rounded-lg text-sm" x-text="error" style="display: none;"></div>
                <form @submit.prevent="register" class="flex flex-col gap-5">
                    <label class="flex flex-col">
                        <p class="text-text-main-light dark:text-white text-sm font-semibold pb-2">Full Name</p>
                        <input type="text" x-model="name" autofocus placeholder="Enter your full name" required class="w-full rounded-lg text-text-main-light dark:text-white bg-background-light dark:bg-[#0e1b17] border border-gray-200 dark:border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 placeholder:text-gray-400">
                        <p x-show="errors.name" class="text-red-500 text-xs mt-1" x-text="errors.name?.[0]" style="display: none;"></p>
                    </label>
                    <label class="flex flex-col">
                        <p class="text-text-main-light dark:text-white text-sm font-semibold pb-2">Email Address</p>
                        <input type="email" x-model="email" placeholder="name@example.com" required class="w-full rounded-lg text-text-main-light dark:text-white bg-background-light dark:bg-[#0e1b17] border border-gray-200 dark:border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 placeholder:text-gray-400">
                        <p x-show="errors.email" class="text-red-500 text-xs mt-1" x-text="errors.email?.[0]" style="display: none;"></p>
                    </label>
                    <label class="flex flex-col">
                        <p class="text-text-main-light dark:text-white text-sm font-semibold pb-2">Password</p>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" x-model="password" placeholder="Create a password" required class="w-full rounded-lg text-text-main-light dark:text-white bg-background-light dark:bg-[#0e1b17] border border-gray-200 dark:border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 pr-12 placeholder:text-gray-400">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary">
                                <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        <p x-show="errors.password" class="text-red-500 text-xs mt-1" x-text="errors.password?.[0]" style="display: none;"></p>
                    </label>
                    <label class="flex flex-col">
                        <p class="text-text-main-light dark:text-white text-sm font-semibold pb-2">Confirm Password</p>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" x-model="password_confirmation" placeholder="Confirm your password" required class="w-full rounded-lg text-text-main-light dark:text-white bg-background-light dark:bg-[#0e1b17] border border-gray-200 dark:border-gray-700 focus:border-primary focus:ring-1 focus:ring-primary h-12 px-4 pr-12 placeholder:text-gray-400">
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary">
                                <span class="material-symbols-outlined text-[20px]" x-text="showConfirmPassword ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </label>
                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" x-model="agreeTerms" class="rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary dark:bg-[#0e1b17] bg-white">
                        <p class="text-sm text-text-sub-light dark:text-gray-400">I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a></p>
                    </div>
                    <button type="submit" :disabled="loading || !agreeTerms" class="mt-4 flex w-full cursor-pointer items-center justify-center rounded-lg h-12 px-4 bg-primary hover:bg-emerald-500 text-white text-sm font-bold transition-colors shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Create Account</span>
                        <span x-show="loading" class="material-symbols-outlined animate-spin" style="display: none;">progress_activity</span>
                    </button>
                </form>
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-gray-700"></div></div>
                </div>
                <div class="text-center">
                    <p class="text-text-sub-light dark:text-gray-400 text-sm">
                        Already have an account? <a href="/login" class="text-primary font-bold hover:underline ml-1">Login</a>
                    </p>
                </div>
            </div>
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400 dark:text-gray-500">© 2024 Siomay Manager. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
