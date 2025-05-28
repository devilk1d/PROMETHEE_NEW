<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">SPK PROMETHEE</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('criteria.index') }}">Criteria</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('alternatives.index') }}">Alternatives</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('decisions.index') }}">Decisions</a>
                </li>
            </ul>
        </div>
    </div>
</nav>