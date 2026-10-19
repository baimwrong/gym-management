<nav class="sidebar-nav">
    <div class="sidebar-heading">Operations</div>
    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('attendance.kiosk') }}" class="sidebar-link {{ request()->routeIs('attendance.kiosk') ? 'active' : '' }}">
        <i class="fa-solid fa-id-card-clip text-warning"></i>
        <span>Access Kiosk</span>
    </a>
    <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Attendance Log</span>
    </a>

    <div class="sidebar-heading">Membership</div>
    <a href="{{ route('members.index') }}" class="sidebar-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
        <i class="fa-solid fa-users"></i>
        <span>Members Directory</span>
    </a>
    <a href="{{ route('plans.index') }}" class="sidebar-link {{ request()->routeIs('plans.*') ? 'active' : '' }}">
        <i class="fa-solid fa-tags"></i>
        <span>Membership Plans</span>
    </a>

    <div class="sidebar-heading">Coaching & Activities</div>
    <a href="{{ route('classes.index') }}" class="sidebar-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
        <i class="fa-solid fa-calendar-days"></i>
        <span>Classes & Timetable</span>
    </a>
    <a href="{{ route('trainers.index') }}" class="sidebar-link {{ request()->routeIs('trainers.*') ? 'active' : '' }}">
        <i class="fa-solid fa-user-ninja"></i>
        <span>Personal Trainers</span>
    </a>
    <a href="{{ route('workouts.index') }}" class="sidebar-link {{ request()->routeIs('workouts.*') ? 'active' : '' }}">
        <i class="fa-solid fa-clipboard-list"></i>
        <span>Workout Routines</span>
    </a>
    <a href="{{ route('diets.index') }}" class="sidebar-link {{ request()->routeIs('diets.*') ? 'active' : '' }}">
        <i class="fa-solid fa-apple-whole"></i>
        <span>Nutrition Plans</span>
    </a>

    <div class="sidebar-heading">Retail & Facility</div>
    <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos.index') ? 'active' : '' }}">
        <i class="fa-solid fa-cash-register text-info"></i>
        <span>Pro-Shop POS</span>
    </a>
    <a href="{{ route('pos.products') }}" class="sidebar-link {{ request()->routeIs('pos.products') ? 'active' : '' }}">
        <i class="fa-solid fa-boxes-stacked"></i>
        <span>Inventory Catalog</span>
    </a>
    <a href="{{ route('invoices.index') }}" class="sidebar-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <span>Invoices & Billing</span>
    </a>
    <a href="{{ route('lockers.index') }}" class="sidebar-link {{ request()->routeIs('lockers.*') ? 'active' : '' }}">
        <i class="fa-solid fa-key"></i>
        <span>Lockers</span>
    </a>
    <a href="{{ route('equipment.index') }}" class="sidebar-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
        <i class="fa-solid fa-screwdriver-wrench"></i>
        <span>Gym Equipment</span>
    </a>

    <div class="sidebar-heading">Analytics & Config</div>
    <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-line"></i>
        <span>Financial Reports</span>
    </a>
    @if(auth()->user()?->isManager())
    <a href="{{ route('import.index') }}" class="sidebar-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
        <i class="fa-solid fa-file-import text-success"></i>
        <span>Bulk Data Import</span>
    </a>
    <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <i class="fa-solid fa-sliders"></i>
        <span>System Settings</span>
    </a>
    @endif
</nav>
