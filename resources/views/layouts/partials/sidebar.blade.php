<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('home') }}">
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('criteria.index') }}">
                    Criteria Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('alternatives.index') }}">
                    Alternative Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('decisions.index') }}">
                    Decision Analysis
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link btn btn-primary text-white" href="{{ route('decisions.calculate') }}">
                    New PROMETHEE Analysis
                </a>
            </li>
        </ul>
    </div>
</div>