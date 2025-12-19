<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Siomay Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-display antialiased bg-background-light dark:bg-background-dark" x-data="{
    email: '',
    password: '',
    showPassword: false,
    loading: false,
    error: null,
    async login() {
        this.loading = true;
        this.error = null;
        try {
            const response = await fetch('/api/v1/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email: this.email, password: this.password })
            });
            const data = await response.json();
            if (response.ok) {
                localStorage.setItem('token', data.data.token);
                window.location.href = '/';
            } else {
                this.error = data.message || 'Login failed';
            }
        } catch (e) { this.error = 'An error occurred'; }
        finally { this.loading = false; }
    }
}">
    <div class="min-h-screen flex w-full">
        <!-- Left Side: Hero Image -->
        <div class="hidden lg:flex w-1/2 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent z-10"></div>
            <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1UtArtW78q-QWq4fRRfAN04TlN_fJRWQYKF91eWa1vAed-4H3gqO1KI4XQbBrQh7QUVUywR96JoxwakYmERB6tFlUheElbHpSfewZUQt2rKD_1AI3llIsuXzdNLAe0NVP-2P3wWaCMNN7vj0sXyUw40W_eWdeVcUuMgqp0qn1791bcvBxy1JTtVmV28_BKKrs9VeERbKKeRJKitUkNXXjGBN_3Zgxw16FM2isa4WzdDoHUrRzdPrN9ZiJ8Ru_S2_EiVBcEaTLXcU" alt="Dimsum" class="absolute inset-0 h-full w-full object-cover opacity-90">
            <div class="relative z-20 flex flex-col justify-end p-16 h-full text-white">
                <div class="flex items-center gap-4 mb-6">
                    <div class="bg-primary/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary text-4xl">restaurant</span>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Siomay Manager</h1>
                </div>
                <blockquote class="text-xl font-medium leading-relaxed max-w-lg mb-4">
                    "Streamline your kitchen, optimize your inventory, and delight your customers with every bite."
                </blockquote>
                <p class="text-lg text-white/70">The all-in-one platform for modern food production.</p>
            </div>
        </div>
        <!-- Right Side: Login Form -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 lg:p-12 bg-white dark:bg-[#152822]">
            <!-- Mobile Logo -->
            <div class="lg:hidden flex items-center gap-3 mb-8">
                <div class="bg-primary/20 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-3xl">restaurant</span>
                </div>
                <h1 class="text-2xl font-bold text-text-main-light dark:text-white">Siomay Manager</h1>
            </div>
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold tracking-tight text-text-main-light dark:text-white">Welcome Back</h2>
                    <p class="mt-2 text-text-sub-light dark:text-gray-400">Manage your production and sales seamlessly.</p>
                </div>
                <form @submit.prevent="login" class="mt-8 space-y-6">
                    <div x-show="error" class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-3 rounded-lg text-sm" x-text="error" style="display: none;"></div>
                    <div class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-text-main-light dark:text-gray-200 mb-2">Email Address</label>
                            <input id="email" type="email" x-model="email" placeholder="Enter your email address" required class="block w-full rounded-lg border-0 py-3 px-4 text-text-main-light dark:text-white shadow-sm ring-1 ring-inset ring-gray-200 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary bg-background-light dark:bg-[#0e1b17]">
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-sm font-medium text-text-main-light dark:text-gray-200">Password</label>
                            </div>
                            <div class="relative">
                                <input id="password" :type="showPassword ? 'text' : 'password'" x-model="password" placeholder="Enter your password" required class="block w-full rounded-lg border-0 py-3 pl-4 pr-12 text-text-main-light dark:text-white shadow-sm ring-1 ring-inset ring-gray-200 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary bg-background-light dark:bg-[#0e1b17]">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-primary">
                                    <span class="material-symbols-outlined text-[20px]" x-text="showPassword ? 'visibility_off' : 'visibility'"></span>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                                <label for="remember-me" class="ml-2 block text-sm text-text-main-light dark:text-gray-300">Remember me</label>
                            </div>
                            <a href="#" class="text-sm font-medium text-primary hover:underline">Forgot password?</a>
                        </div>
                    </div>
                    <button type="submit" :disabled="loading" class="flex w-full justify-center rounded-lg bg-primary px-3 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all transform active:scale-[0.98] disabled:opacity-50">
                        <span x-show="!loading">Log In</span>
                        <span x-show="loading" class="material-symbols-outlined animate-spin" style="display: none;">progress_activity</span>
                    </button>
                </form>
                <p class="mt-10 text-center text-sm text-text-sub-light dark:text-gray-400">
                    Don't have an account? <a href="/register" class="font-semibold text-primary hover:underline">Create Account</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
