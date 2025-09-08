
@extends('frontend.layouts.app')

@section('content')

<style>
    /* Custom styles replacing Tailwind classes */
    .container {
        width: 100%;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    .p-4 {
        padding: 1rem;
    }
    .bg-gray-100 {
        background-color: #f3f4f6;
    }
    .text-2xl {
        font-size: 1.5rem;
        line-height: 2rem;
    }
    .font-bold {
        font-weight: 700;
    }
    .mb-4 {
        margin-bottom: 1rem;
    }
    .flex {
        display: flex;
    }
    .gap-4 {
        gap: 1rem;
    }
    .border {
        border: 1px solid #d1d5db;
    }
    .rounded {
        border-radius: 0.25rem;
    }
    .w-full {
        width: 100%;
    }
    .max-w-md {
        max-width: 28rem;
    }
    .bg-blue-600 {
        background-color: #2563eb;
    }
    .text-white {
        color: #ffffff;
    }
    .px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    .py-2 {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    .hover-bg-blue-700:hover {
        background-color: #1d4ed8;
    }
    .bg-white {
        background-color: #ffffff;
    }
    .shadow {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    }
    .space-y-4 > :not(:last-child) {
        margin-bottom: 1rem;
    }
    .text-green-600 {
        color: #16a34a;
    }
    .text-blue-600 {
        color: #2563eb;
    }
    .hover-text-blue-800:hover {
        color: #1e40af;
    }
    .bg-gray-50 {
        background-color: #f9fafb;
    }

    /* Accordion and tab styles */
    .accordion-header:hover {
        background-color: #e5e7eb;
    }
    .accordion-content {
        display: none;
    }
    .accordion-content.active {
        display: block !important;
        min-height: 50px;
        overflow: visible;
    }
    .tab {
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-bottom: 2px solid transparent;
    }
    .tab.active {
        border-bottom: 2px solid #1e40af;
        color: #1e40af;
        font-weight: bold;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }

    /* Sidebar dropdown styles (adjust class names to match your sidebar) */
    .sidebar .dropdown-menu {
        display: none;
    }
    .sidebar .dropdown-menu.active {
        display: block !important;
        min-height: 50px;
        overflow: visible;
        z-index: 1000;
    }
    .sidebar .dropdown-toggle {
        cursor: pointer;
        padding: 0.5rem 1rem;
    }
    .sidebar .dropdown-toggle:hover {
        background-color: #e5e7eb;
    }
</style>

<div class="container p-4 bg-gray-100">
    <h1 class="text-2xl font-bold mb-4">DOLE Archived Cases</h1>
    
    <!-- Search and Filter -->
    <div class="mb-4 flex gap-4">
        <input type="text" id="searchInput" placeholder="Search by Case No. or Establishment Name" 
               class="p-2 border rounded w-full max-w-md">
        <select class="p-2 border rounded">
            <option>Filter by Status</option>
            <option>Closed</option>
            <option>Resolved</option>
            <option>Settled</option>
        </select>
        <button class="bg-blue-600 text-white px-4 py-2 rounded hover-bg-blue-700">
            Export Selected
        </button>
    </div>

    <!-- Accordion List -->
    <div id="caseList" class="space-y-4">
        <!-- Case 1 -->
        <div class="bg-white shadow rounded">
            <div class="accordion-header p-4 flex justify-between items-center cursor-pointer">
                <div>
                    <span class="font-bold">Inspection ID:</span> INS-2025-001<br>
                    <span class="font-bold">Case No.:</span> CASE-2025-123<br>
                    <span class="font-bold">Establishment:</span> ABC Corporation<br>
                    <span class="font-bold">Date Archived:</span> 2025-09-01<br>
                    <span class="font-bold">Status:</span> <span class="text-green-600">Closed</span>
                </div>
                <div>
                    <button class="text-blue-600 hover-text-blue-800">View Details</button>
                </div>
            </div>
            <div class="accordion-content p-4 bg-gray-50">
                <!-- Tab Navigation -->
                <div class="flex border-b mb-4">
                    <div class="tab active" data-tab="stage1">Stage 1: Inspection</div>
                    <div class="tab" data-tab="stage2">Stage 2: Docketing</div>
                    <div class="tab" data-tab="stage3">Stage 3: Hearing</div>
                </div>
                <!-- Tab Content -->
                <div id="stage1" class="tab-content active">
                    <h3 class="font-bold">Inspection Details</h3>
                    <p><strong>Date:</strong> 2025-08-01</p>
                    <p><strong>Inspector:</strong> John Doe</p>
                    <p><strong>Findings:</strong> Non-compliance with labor standards detected.</p>
                    <p><strong>Notes:</strong> Initial inspection completed with minor violations noted.</p>
                </div>
                <div id="stage2" class="tab-content">
                    <h3 class="font-bold">Docketing Details</h3>
                    <p><strong>Date:</strong> 2025-08-10</p>
                    <p><strong>Officer:</strong> Jane Smith</p>
                    <p><strong>Case Filed:</strong> CASE-2025-123</p>
                    <p><strong>Notes:</strong> Case docketed and scheduled for hearing.</p>
                </div>
                <div id="stage3" class="tab-content">
                    <h3 class="font-bold">Hearing Details</h3>
                    <p><strong>Date:</strong> 2025-08-20</p>
                    <p><strong>Judge:</strong> Michael Brown</p>
                    <p><strong>Outcome:</strong> Case resolved with compliance order.</p>
                    <p><strong>Notes:</strong> Establishment complied with all requirements.</p>
                </div>
            </div>
        </div>

        <!-- Case 2 (Sample) -->
        <div class="bg-white shadow rounded">
            <div class="accordion-header p-4 flex justify-between items-center cursor-pointer">
                <div>
                    <span class="font-bold">Inspection ID:</span> INS-2025-002<br>
                    <span class="font-bold">Case No.:</span> CASE-2025-124<br>
                    <span class="font-bold">Establishment:</span> XYZ Industries<br>
                    <span class="font-bold">Date Archived:</span> 2025-09-05<br>
                    <span class="font-bold">Status:</span> <span class="text-green-600">Resolved</span>
                </div>
                <div>
                    <button class="text-blue-600 hover-text-blue-800">View Details</button>
                </div>
            </div>
            <div class="accordion-content p-4 bg-gray-50">
                <div class="flex border-b mb-4">
                    <div class="tab active" data-tab="stage1-2">Stage 1: Inspection</div>
                    <div class="tab" data-tab="stage2-2">Stage 2: Docketing</div>
                    <div class="tab" data-tab="stage3-2">Stage 3: Hearing</div>
                </div>
                <div id="stage1-2" class="tab-content active">
                    <h3 class="font-bold">Inspection Details</h3>
                    <p><strong>Date:</strong> 2025-08-03</p>
                    <p><strong>Inspector:</strong> Sarah Lee</p>
                    <p><strong>Findings:</strong> Safety violations detected.</p>
                    <p><strong>Notes:</strong> Inspection completed with recommendations.</p>
                </div>
                <div id="stage2-2" class="tab-content">
                    <h3 class="font-bold">Docketing Details</h3>
                    <p><strong>Date:</strong> 2025-08-12</p>
                    <p><strong>Officer:</strong> Mark Wilson</p>
                    <p><strong>Case Filed:</strong> CASE-2025-124</p>
                    <p><strong>Notes:</strong> Case prepared for hearing.</p>
                </div>
                <div id="stage3-2" class="tab-content">
                    <h3 class="font-bold">Hearing Details</h3>
                    <p><strong>Date:</strong> 2025-08-25</p>
                    <p><strong>Judge:</strong> Emily Davis</p>
                    <p><strong>Outcome:</strong> Case settled with agreement.</p>
                    <p><strong>Notes:</strong> Full compliance achieved.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sidebar dropdown toggle (adjust class names to match your sidebar)
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default link behavior if applicable
            const menu = toggle.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.classList.toggle('active');
                console.log('Sidebar dropdown toggled:', menu.classList.contains('active') ? 'Visible' : 'Hidden');
            }
        });
    });

    // Accordion Toggle
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            const content = header.nextElementSibling;
            content.classList.toggle('active');
        });
    });

    // Tab Switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const accordion = tab.closest('.accordion-content');
            accordion.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            accordion.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            const tabContentId = tab.getAttribute('data-tab');
            accordion.querySelector(`#${tabContentId}`).classList.add('active');
        });
    });

    // Basic Search Functionality
    document.getElementById('searchInput').addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const cases = document.querySelectorAll('#caseList > div');
        cases.forEach(caseItem => {
            const text = caseItem.textContent.toLowerCase();
            caseItem.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>

@stop
