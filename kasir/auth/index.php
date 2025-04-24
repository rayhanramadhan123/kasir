<?php include '../backend/login.php';?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login</title>
    <link rel="shortcut icon" href="../asset/image/logo.png" />
</head>
<body class="p-8 bg-gray-100">
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <img class="mx-auto h-20 w-auto" src="https://dogmitindonesia.com/wp-content/uploads/2019/02/login-icon-1.png" alt="Login Icon">
        <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Login Oke Shoes</h2>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <?php if ($error): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form class="space-y-6" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div>
                <label for="username" class="block text-sm font-medium leading-6 text-gray-900">Username</label>
                <div class="mt-2">
                    <input id="username" 
                           name="username" 
                           type="text" 
                           placeholder="Masukkan Username" 
                           required 
                           value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                           class="mt-4 px-3 py-2 bg-white border shadow-xl border-slate-300 placeholder-slate-400 focus:outline-cyan-500 focus:border-sky-500 focus:ring-sky-500 block w-full rounded-md sm:text-sm focus:ring-1 hover:-translate-y-1 delay-150 duration-200">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                <div class="mt-2 relative">
                    <input id="password" 
                           name="password" 
                           type="password" 
                           placeholder="Masukkan Password" 
                           required 
                           class="mt-3 px-3 py-2 bg-white border shadow-xl border-slate-300 placeholder-slate-400 focus:outline-pink-500 focus:border-sky-500 focus:ring-sky-500 block w-full rounded-md sm:text-sm focus:ring-1 hover:-translate-y-1 delay-150 duration-200">
                    <button 
                        type="button" 
                        id="togglePassword" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                        <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <button type="submit" class="flex w-full justify-center rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Log in</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';

        eyeIcon.innerHTML = isPassword ? 
            `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>` :
            `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
    });
</script>


</body>
</html>