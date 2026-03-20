<header class="header">
    <div class="container header-container">
        <div class="logo">
            <a href="{{ route('landing') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="U-LITS Logo">
                <span>U-LITS</span>
            </a>
        </div>

        <nav class="navbar">
            <button class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="nav-menu">
                <li><a href="#learn-more">About</a></li>
                <li><a href="#registers">Registers</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="#" class="btn btn-secondary">Login</a>
        </div>
    </div>
</header>
